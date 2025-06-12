<?php

namespace App\Console\Commands;

use Illuminate\Console\Application;
use Illuminate\Console\Command;

class FallbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fallback:handle {command : The command that was attempted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles fallback for missing commands';

    /**
     * Map of known fallback commands and their alternatives or explanations.
     *
     * @var array
     */
    protected $fallbackMap = [
        // Dev tool commands that should run directly
        'npm' => [
            'message' => 'Bsidlify does not handle npm commands directly.',
            'suggestion' => 'Please run npm commands directly from your terminal.',
        ],
        'yarn' => [
            'message' => 'Bsidlify does not handle yarn commands directly.',
            'suggestion' => 'Please run yarn commands directly from your terminal.',
        ],
        'composer' => [
            'message' => 'Bsidlify does not handle composer commands directly.',
            'suggestion' => 'Please run composer commands directly from your terminal.',
        ],
        'pnpm' => [
            'message' => 'Bsidlify does not handle pnpm commands directly.',
            'suggestion' => 'Please run pnpm commands directly from your terminal.',
        ],
        'bun' => [
            'message' => 'Bsidlify does not handle bun commands directly.',
            'suggestion' => 'Please run bun commands directly from your terminal.',
        ],
        
        // Renamed or removed commands
        'update' => [
            'message' => 'The update command has been removed from Bsidlify.',
            'suggestion' => 'Use "composer update" to update your dependencies.',
        ],
        'preset' => [
            'message' => 'The preset command is not available in Bsidlify.',
            'suggestion' => 'You can install UI packages manually using npm or yarn.',
        ],
        'tail' => [
            'message' => 'The tail command is not available in Bsidlify.',
            'suggestion' => 'Use "bsidlify pail" to view and monitor logs.',
        ],
        'artisan' => [
            'message' => 'The artisan command has been renamed in Bsidlify.',
            'suggestion' => 'Use "./bsidlify" instead of "./artisan".',
        ],
        
        // Deprecated commands
        'app:name' => [
            'message' => 'The app:name command is not available in Bsidlify.',
            'suggestion' => 'Manually update namespace references if needed.',
        ],
        'make:auth' => [
            'message' => 'The make:auth command is not available in Bsidlify.',
            'suggestion' => 'Use "bsidlify install:auth" to install authentication scaffolding.',
        ],
        'make:console' => [
            'message' => 'The make:console command is not available in Bsidlify.',
            'suggestion' => 'Use "bsidlify make:command" to create a new console command.',
        ],
        
        // Common typos or alternative names
        'migrate:refresh' => [
            'message' => 'Did you mean one of these?',
            'suggestion' => 'bsidlify migrate:fresh or bsidlify migrate:reset',
        ],
        'c:c' => [
            'message' => 'Command shorthand "c:c" is not available.',
            'suggestion' => 'Use "bsidlify cache:clear" instead.',
        ],
        'r:c' => [
            'message' => 'Command shorthand "r:c" is not available.',
            'suggestion' => 'Use "bsidlify route:clear" instead.',
        ],
        'v:c' => [
            'message' => 'Command shorthand "v:c" is not available.',
            'suggestion' => 'Use "bsidlify view:clear" instead.',
        ],
        'c:cache' => [
            'message' => 'Did you mean "cache:clear"?',
            'suggestion' => 'Use "bsidlify cache:clear" to clear the application cache.',
        ],
        'serve:start' => [
            'message' => 'Did you mean "serve"?',
            'suggestion' => 'Use "bsidlify serve" to start the development server.',
        ],
        'run' => [
            'message' => 'The "run" command is not available.',
            'suggestion' => 'Use "bsidlify serve" to start the development server.',
        ],
        'start' => [
            'message' => 'The "start" command is not available.',
            'suggestion' => 'Use "bsidlify serve" to start the development server.',
        ],
    ];
    
    /**
     * Map of namespace prefixes and their related messages
     * Used to show helpful suggestions for partial namespaces
     * 
     * @var array
     */
    protected $namespaceMap = [
        'make' => [
            'message' => 'The "make" command needs a specific type to generate.',
            'suggestion' => 'Try one of these: make:controller, make:model, make:migration, make:middleware',
        ],
        'db' => [
            'message' => 'The "db" command has several database-related subcommands.',
            'suggestion' => 'Try one of these: db:seed, db:wipe, db:monitor, db:table, db:show',
        ],
        'cache' => [
            'message' => 'The "cache" command has several cache-related subcommands.',
            'suggestion' => 'Try one of these: cache:clear, cache:forget, cache:prune-stale-tags',
        ],
        'config' => [
            'message' => 'The "config" command has several config-related subcommands.',
            'suggestion' => 'Try one of these: config:cache, config:clear, config:publish, config:show',
        ],
        'migrate' => [
            'message' => 'The "migrate" command has several migration-related subcommands.',
            'suggestion' => 'Try one of these: migrate:fresh, migrate:install, migrate:status, migrate:rollback',
        ],
        'queue' => [
            'message' => 'The "queue" command has several queue-related subcommands.',
            'suggestion' => 'Try one of these: queue:work, queue:listen, queue:clear, queue:retry',
        ],
        'route' => [
            'message' => 'The "route" command has several routing-related subcommands.',
            'suggestion' => 'Try one of these: route:list, route:cache, route:clear',
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $command = $this->argument('command');
        
        // Check for exact matches in our fallback map
        if (isset($this->fallbackMap[$command])) {
            $info = $this->fallbackMap[$command];
            
            $this->error($info['message']);
            $this->line('');
            $this->info($info['suggestion']);
            
            return 0;
        }
        
        // Check for namespace prefixes
        foreach ($this->namespaceMap as $prefix => $info) {
            if ($command === $prefix) {
                $this->error($info['message']);
                $this->line('');
                $this->info($info['suggestion']);
                
                // Get all commands that match this prefix to show a more complete list
                $matchingCommands = $this->getCommandsForNamespace($prefix);
                if (!empty($matchingCommands)) {
                    $this->line('');
                    $this->info('Available commands in the "' . $prefix . '" namespace:');
                    
                    foreach ($matchingCommands as $cmd) {
                        $this->line("  <fg=blue>{$cmd}</>");
                    }
                }
                
                return 0;
            }
        }
        
        return Command::FAILURE;
    }
    
    /**
     * Get all commands that match a specific namespace prefix.
     *
     * @param string $namespace
     * @return array
     */
    protected function getCommandsForNamespace(string $namespace): array
    {
        $matches = [];
        $commands = $this->getApplication()->all();
        
        foreach (array_keys($commands) as $name) {
            if (strpos($name, $namespace . ':') === 0) {
                $matches[] = $name;
            }
        }
        
        sort($matches);
        
        return $matches;
    }
    
    /**
     * Get the fallback map.
     *
     * @return array
     */
    public function getFallbackMap()
    {
        return $this->fallbackMap;
    }
    
    /**
     * Get the namespace map.
     *
     * @return array
     */
    public function getNamespaceMap()
    {
        return $this->namespaceMap;
    }
} 