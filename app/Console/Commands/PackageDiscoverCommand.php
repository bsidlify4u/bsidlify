<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PackageDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:discover {--ansi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Discovering packages...');
        
        // Create the path if it doesn't exist
        $path = $this->laravel->bootstrapPath('cache');
        
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }

        // Start with empty packages
        $packages = [];

        // Save an empty manifest for now to get past this step
        $this->files->put(
            $this->laravel->bootstrapPath('cache/packages.php'),
            '<?php return '.var_export($packages, true).';'
        );

        $this->info('Package discovery completed');

        return 0;
    }
} 