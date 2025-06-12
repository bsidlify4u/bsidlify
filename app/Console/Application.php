<?php

namespace App\Console;

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Console\ClosureCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use App\Console\Commands\FallbackCommand;

class Application extends ConsoleApplication
{
    /**
     * Create a new console application.
     *
     * @param  \Illuminate\Contracts\Container\Container  $laravel
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  string  $version
     * @return void
     */
    public function __construct(Container $laravel, Dispatcher $events, $version)
    {
        parent::__construct($laravel, $events, $version);
        
        // Override the name to Bsidlify Framework
        $this->setName('Bsidlify Framework');
        
        // Make sure to load all commands
        $this->bootstrap();
        
        // Add global --debug option to all commands
        $this->getDefinition()->addOption(
            new \Symfony\Component\Console\Input\InputOption(
                'debug',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Enable debug mode'
            )
        );
    }
    
    /**
     * Bootstrap the console application.
     *
     * @return void
     */
    protected function bootstrap()
    {
        if (! $this->laravel->hasBeenBootstrapped()) {
            $this->laravel->bootstrapWith($this->bootstrappers());
        }

        $this->laravel->loadDeferredProviders();

        if (isset($this->laravel['events'])) {
            $events = $this->laravel['events'];

            $events->dispatch(new \Illuminate\Console\Events\ArtisanStarting($this));
        }
        
        // Load commands from the console kernel
        $kernel = $this->laravel->make(Kernel::class);
        
        foreach ($kernel->all() as $command) {
            if ($command instanceof ClosureCommand) {
                $command->setLaravel($this->laravel);
            }
            
            $this->add($command);
        }
    }
    
    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return [
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ];
    }
    
    /**
     * Finds a command by name or alias.
     *
     * @param string $name A command name or a command alias
     *
     * @return \Symfony\Component\Console\Command\Command A Command object
     *
     * @throws CommandNotFoundException When command name is incorrect or ambiguous
     */
    public function find(string $name): \Symfony\Component\Console\Command\Command
    {
        try {
            return parent::find($name);
        } catch (CommandNotFoundException $e) {
            // Check if we have a known fallback for this command
            $fallbackCommand = $this->laravel->make(FallbackCommand::class);
            $fallbackMap = $fallbackCommand->getFallbackMap();
            
            if (isset($fallbackMap[$name])) {
                // Create a simple Symfony command directly
                $info = $fallbackMap[$name];
                
                $command = new \Symfony\Component\Console\Command\Command($name);
                $command->setHelperSet($this->getHelperSet());
                
                $command->setCode(function ($input, $output) use ($info) {
                    $output->writeln('<fg=red>' . $info['message'] . '</>');
                    $output->writeln('');
                    $output->writeln('<fg=green>' . $info['suggestion'] . '</>');
                    return 0;
                });
                
                return $command;
            }
            
            // Let parent handle it otherwise
            throw $e;
        }
    }
    
    /**
     * Runs the current application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an error code
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        try {
            return parent::run($input, $output);
        } catch (CommandNotFoundException $e) {
            if (null === $input) {
                throw $e;
            }

            $output = $output ?? new \Symfony\Component\Console\Output\ConsoleOutput();
            
            // Extract the command name that wasn't found
            $commandName = $this->extractCommandName($input);
            
            // Check if we have a known fallback for this command
            $fallbackCommand = $this->laravel->make(FallbackCommand::class);
            $fallbackMap = $fallbackCommand->getFallbackMap();
            
            if (isset($fallbackMap[$commandName])) {
                $info = $fallbackMap[$commandName];
                $output->writeln('<fg=red>' . $info['message'] . '</>');
                $output->writeln('');
                $output->writeln('<fg=green>' . $info['suggestion'] . '</>');
                return SymfonyCommand::SUCCESS;
            }
            
            // Check for namespace prefixes
            if (method_exists($fallbackCommand, 'getNamespaceMap')) {
                $namespaceMap = $fallbackCommand->getNamespaceMap();
                
                if (isset($namespaceMap[$commandName])) {
                    $info = $namespaceMap[$commandName];
                    $output->writeln('<fg=red>' . $info['message'] . '</>');
                    $output->writeln('');
                    $output->writeln('<fg=green>' . $info['suggestion'] . '</>');
                    
                    // Show all available commands in this namespace
                    $namespaceCommands = $this->getCommandsWithPrefix($commandName);
                    if (!empty($namespaceCommands)) {
                        $output->writeln('');
                        $output->writeln('<fg=green>Available commands in the "' . $commandName . '" namespace:</>');
                        
                        foreach ($namespaceCommands as $cmd) {
                            $output->writeln("  <fg=blue>{$cmd}</>");
                        }
                    }
                    
                    return SymfonyCommand::SUCCESS;
                }
            }
            
            // Use built-in suggestion system
            // We're just customizing the output format
            $message = $e->getMessage();
            $output->writeln("<fg=red>Command \"{$commandName}\" is not defined.</>");
            
            // Check if the exception message contains alternative suggestions
            if (preg_match('/Did you mean this\?(.+)/s', $message, $matches)) {
                $output->writeln('<fg=green>Did you mean one of these?</>');
                
                $suggestions = explode("\n", trim($matches[1]));
                foreach ($suggestions as $suggestion) {
                    if (trim($suggestion)) {
                        // Remove the leading dash/bullet point if any
                        $suggestion = preg_replace('/^\s*[\-\*]\s*/', '', trim($suggestion));
                        $output->writeln("  <fg=blue>{$suggestion}</>");
                    }
                }
                
                $output->writeln('');
            } else {
                // If no suggestions provided, use our own algorithm
                $alternatives = $this->findAlternatives($commandName);
                
                if (count($alternatives) > 0) {
                    $output->writeln('<fg=green>Did you mean one of these?</>');
                    
                    foreach ($alternatives as $alternative) {
                        $output->writeln("  <fg=blue>{$alternative}</>");
                    }
                    
                    $output->writeln('');
                }
            }
            
            // Provide general help
            $output->writeln("<fg=yellow>Run \"./bsidlify list\" to see available commands.</>");
            
            return SymfonyCommand::FAILURE;
        }
    }
    
    /**
     * Extract command name from input.
     *
     * @param InputInterface $input
     * @return string
     */
    protected function extractCommandName(InputInterface $input): string
    {
        return $input->getFirstArgument() ?: '';
    }
    
    /**
     * Find alternative commands based on similarity.
     *
     * @param string $name
     * @param int $threshold
     * @return array
     */
    protected function findAlternatives(string $name, int $threshold = 4): array
    {
        $alternatives = [];
        
        // Get all command names
        $commandNames = array_keys($this->all());
        
        foreach ($commandNames as $commandName) {
            // Calculate Levenshtein distance between the requested name and the actual command name
            $distance = levenshtein($name, $commandName);
            
            // Consider only commands with a distance lower than the threshold
            if ($distance <= $threshold) {
                $alternatives[$commandName] = $distance;
            }
        }
        
        // Sort by distance
        asort($alternatives);
        
        // Return only command names, limited to 5 suggestions
        return array_slice(array_keys($alternatives), 0, 5);
    }
    
    /**
     * Get all commands with a specific prefix.
     *
     * @param string $prefix
     * @return array
     */
    protected function getCommandsWithPrefix(string $prefix): array
    {
        $matches = [];
        $commands = $this->all();
        
        foreach (array_keys($commands) as $name) {
            if (strpos($name, $prefix . ':') === 0) {
                $matches[] = $name;
            }
        }
        
        sort($matches);
        
        return $matches;
    }
} 