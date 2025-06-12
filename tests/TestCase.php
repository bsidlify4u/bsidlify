<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Exception;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up the test case.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Check for .env file and APP_KEY before continuing
        $this->checkEnvironmentSetup();
        
        parent::setUp();
    }
    
    /**
     * Check if the environment is properly set up before running tests.
     *
     * @return void
     */
    protected function checkEnvironmentSetup(): void
    {
        // Skip in CI environments
        if ($this->isRunningInCI()) {
            return;
        }
        
        $basePath = $this->getBasePath();
        $envPath = $basePath . '/.env';
        
        // Check if .env file exists
        if (!file_exists($envPath)) {
            $this->displayEnvironmentError(
                'Missing Environment File', 
                "The .env file does not exist.", 
                [
                    "Create an environment file by running:",
                    "cp .env.example .env",
                    "php bsidlify key:generate"
                ],
                true // Force exit for missing .env file
            );
        }
        
        // Check if APP_KEY is set in .env
        $envContent = file_get_contents($envPath);
        
        // Parse the .env file for APP_KEY
        $appKey = $this->getEnvValue($envContent, 'APP_KEY');
        
        // If APP_KEY is empty or not set properly
        if (empty($appKey)) {
            $this->displayEnvironmentError(
                'Missing Application Key',
                "The APP_KEY environment variable is not set in .env.",
                [
                    "Generate an application key by running:",
                    "php bsidlify key:generate"
                ],
                true // Force exit for missing APP_KEY as well
            );
        }
    }
    
    /**
     * Get an environment value from a .env file content.
     *
     * @param string $content
     * @param string $key
     * @return string|null
     */
    protected function getEnvValue(string $content, string $key): ?string
    {
        $pattern = "/^{$key}=(.*)$/m";
        if (preg_match($pattern, $content, $matches)) {
            $value = trim($matches[1]);
            return empty($value) ? null : $value;
        }
        
        return null;
    }
    
    /**
     * Get the base path of the application.
     *
     * @return string
     */
    protected function getBasePath(): string
    {
        // In a test environment, Laravel may not be fully booted
        // So we determine the base path directly
        return dirname(__DIR__);
    }
    
    /**
     * Check if running in a CI environment.
     * 
     * @return bool
     */
    protected function isRunningInCI(): bool
    {
        return getenv('CI') === 'true' || 
               !empty(getenv('GITHUB_ACTIONS')) || 
               !empty(getenv('GITLAB_CI')) || 
               !empty(getenv('TRAVIS'));
    }
    
    /**
     * Display a formatted error message and stop test execution if needed.
     *
     * @param string $title Error title
     * @param string $message Error description
     * @param array $solutions Suggested solutions
     * @param bool $shouldExit Whether to exit immediately (true) or throw an exception (false)
     * @return void
     * @throws \Exception
     */
    protected function displayEnvironmentError(string $title, string $message, array $solutions, bool $shouldExit = false): void
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
        
        if ($shouldExit) {
            // Completely exit the test process for critical errors
            // $output->writeln("<fg=red>Tests cannot continue. Exiting...</>");
            exit(1);
        } else {
            // Skip the test with a clear message for non-critical errors
            throw new Exception("Test skipped: {$message}");
        }
    }
}
