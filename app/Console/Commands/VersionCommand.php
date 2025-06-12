<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version {--compact : Display the version in compact format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the application\'s current version';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info(
            $this->option('compact')
                ? $this->laravel->version()
                : 'Bsidlify Framework ' . $this->laravel->version()
        );
    }
} 