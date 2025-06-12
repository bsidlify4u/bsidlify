<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\SkippedTestError;
use Symfony\Component\Console\Output\ConsoleOutput;

trait ChecksEnvironment
{
    /**
     * Check if the environment is properly set up before running tests.
     *
     * @return void
     * @throws \PHPUnit\Framework\SkippedTestError
     */
    protected function checkEnvironmentSetup(): void
    {
        // First, check if this is a CI environment (GitHub Actions, etc.)
        if ($this->isRunningInCI()) {
            // Skip checks in CI environments which typically set env variables differently
            return;
        }
        
        // Get appropriate .env file based on current environment
        $basePath = base_path();
        $envFile = $this->getEnvironmentFile();
        $envPath = $basePath . '/' . $envFile;
        
        // Check if .env file exists
        if (!File::exists($envPath)) {
            $this->displayEnvironmentError(
                'Missing Environment File', 
                "The {$envFile} file does not exist.", 
                [
                    "Create an environment file by running:",
                    "cp .env.example {$envFile}",
                    "php bsidlify key:generate"
                ]
            );
        }
        
        // Check if APP_KEY is set in .env
        $envContent = File::get($envPath);
        if (empty(env('APP_KEY')) || preg_match('/^APP_KEY=(\s*)$/m', $envContent)) {
            $this->displayEnvironmentError(
                'Missing Application Key',
                "The APP_KEY environment variable is not set in {$envFile}.",
                [
                    "Generate an application key by running:",
                    "php bsidlify key:generate --env=" . $this->getEnvironment()
                ]
            );
        }
    }
    
    /**
     * Get the appropriate .env file based on environment.
     * 
     * @return string
     */
    protected function getEnvironmentFile(): string
    {
        $env = $this->getEnvironment();
        
        if ($env === 'testing') {
            return '.env.testing';
        }
        
        return '.env';
    }
    
    /**
     * Get the current environment.
     * 
     * @return string
     */
    protected function getEnvironment(): string
    {
        return env('APP_ENV', 'testing');
    }
    
    /**
     * Check if running in a CI environment.
     * 
     * @return bool
     */
    protected function isRunningInCI(): bool
    {
        return env('CI') === true || 
               !empty(env('GITHUB_ACTIONS')) || 
               !empty(env('GITLAB_CI')) || 
               !empty(env('TRAVIS'));
    }
    
    /**
     * Display a formatted error message and skip the test.
     *
     * @param string $title Error title
     * @param string $message Error description
     * @param array $solutions Suggested solutions
     * @return void
     * @throws \PHPUnit\Framework\SkippedTestError
     */
    protected function displayEnvironmentError(string $title, string $message, array $solutions): void
    {
        $output = new ConsoleOutput();
        
        // Display colorful error message in the console
        $output->writeln('');
        $output->writeln('<bg=red;fg=white>                                                         </>');
        $output->writeln("<bg=red;fg=white>   {$title}                                    </>");
        $output->writeln('<bg=red;fg=white>                                                         </>');
        $output->writeln('');
        $output->writeln("<fg=red>{$message}</>");
        $output->writeln('');
        
        // Display solutions
        $output->writeln('<fg=green>Solution:</>');
        foreach ($solutions as $step) {
            $output->writeln("  <fg=yellow>{$step}</>");
        }
        $output->writeln('');
        
        // Skip the test with a clear message
        throw new SkippedTestError("Test skipped: {$message}");
    }
} 