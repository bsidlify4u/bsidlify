<?php

namespace App\Foundation;

use App\Console\Application as ConsoleApplication;
use App\Foundation\PackageManifest;
use Illuminate\Foundation\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Filesystem\Filesystem;

class Application extends BaseApplication
{
    /**
     * Bsidlify framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        parent::registerBaseServiceProviders();
        
        // Make sure the package manifest is set up properly for Bsidlify
        $this->singleton('Illuminate\Foundation\PackageManifest', function ($app) {
            return new PackageManifest(
                new Filesystem, $app->basePath(), $app->getCachedPackagesPath()
            );
        });
    }

    /**
     * Override register base bindings to ensure all required bindings
     * are properly registered in the Bsidlify container
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        parent::registerBaseBindings();
        
        // Make sure config is properly bound
        $this->singleton('config', function () {
            return $this->make('Illuminate\Config\Repository');
        });
    }

    /**
     * Get the Bsidlify console application instance.
     *
     * @return \App\Console\Application
     */
    public function getConsoleApplication()
    {
        return new ConsoleApplication($this, $this->make('events'), $this->version());
    }

    /**
     * Handle the incoming console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return int
     */
    public function handleCommand(InputInterface $input)
    {
        try {
            $output = new ConsoleOutput();
            $command = $input->getFirstArgument() ?? '';
            
            $this['events']->dispatch(
                new \Illuminate\Console\Events\CommandStarting(
                    $command,
                    $input,
                    $output
                )
            );
            
            // Set debug mode if requested via command-line
            if ($input->hasParameterOption('--debug')) {
                if (!defined('BSIDLIFY_DEBUG')) {
                    define('BSIDLIFY_DEBUG', true);
                }
                // Also set PHPs error reporting level to show all errors
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }

            $status = $this->make(\Illuminate\Contracts\Console\Kernel::class)->handle(
                $input, $output
            );

            $this['events']->dispatch(
                new \Illuminate\Console\Events\CommandFinished(
                    $command,
                    $input,
                    $output,
                    $status
                )
            );

            return $status;
        } catch (\Throwable $e) {
            // If debug mode is enabled, let the exception bubble up
            if (defined('BSIDLIFY_DEBUG') && BSIDLIFY_DEBUG) {
                throw $e;
            }
            
            $this->reportException($e);
            
            $this->renderException($e);
            
            return 1;
        }
    }
    
    /**
     * Get the Bsidlify version.
     *
     * @return string
     */
    public function version()
    {
        return str_replace('Laravel', 'Bsidlify', parent::version());
    }
    
    /**
     * Report an exception to the exception handler.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function reportException(\Throwable $e)
    {
        $this[ExceptionHandler::class]->report($e);
    }
    
    /**
     * Render an exception to the console.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderException(\Throwable $e)
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln("<fg=red>Error: " . $e->getMessage() . "</>");
        $output->writeln("<fg=yellow>Run with --debug option for more information.</>");
    }
} 