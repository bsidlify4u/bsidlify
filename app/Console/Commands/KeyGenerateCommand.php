<?php

namespace App\Console\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Console\KeyGenerateCommand as BaseKeyGenerateCommand;
use Illuminate\Support\Facades\File;

class KeyGenerateCommand extends BaseKeyGenerateCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $envPath = $this->laravel->environmentFilePath();
        
        // Check if .env file exists
        if (!File::exists($envPath)) {
            $exampleEnvPath = $this->laravel->basePath('.env.example');
            
            if (File::exists($exampleEnvPath)) {
                // Copy from .env.example if it exists
                File::copy($exampleEnvPath, $envPath);
                $this->info('Created .env file from .env.example');
            } else {
                // Create a minimal .env file
                File::put($envPath, "APP_NAME=Bsidlify\nAPP_ENV=local\nAPP_DEBUG=true\n");
                $this->info('Created a new .env file with minimal settings');
            }
        }
        
        return parent::handle();
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        $envPath = $this->laravel->environmentFilePath();
        
        if (!File::exists($envPath)) {
            $this->error("The .env file does not exist. Try running this command again.");
            return false;
        }
        
        $content = file_get_contents($envPath);
        
        // Check if there's already an APP_KEY with any value
        if (preg_match('/^APP_KEY=.+/m', $content)) {
            // Replace existing key with any value
            $replaced = preg_replace('/^APP_KEY=.+$/m', 'APP_KEY='.$key, $content);
        } 
        // Check if there's an empty APP_KEY
        elseif (preg_match('/^APP_KEY=(\s*)$/m', $content)) {
            $replaced = preg_replace('/^APP_KEY=(\s*)$/m', 'APP_KEY='.$key, $content);
        }
        // No APP_KEY found, try to add it after APP_ENV 
        else {
            if (preg_match('/^APP_ENV/m', $content)) {
                $replaced = preg_replace(
                    '/^APP_ENV(.*)$/m', 
                    "APP_ENV$1\nAPP_KEY=$key",
                    $content
                );
            } else {
                // Last resort: add at the beginning
                $replaced = "APP_KEY=$key\n" . $content;
            }
        }

        // Final check if we actually changed anything
        if ($replaced === $content || $replaced === null) {
            $this->error('Unable to set application key. Could not modify the .env file.');
            return false;
        }

        file_put_contents($envPath, $replaced);

        return true;
    }
} 