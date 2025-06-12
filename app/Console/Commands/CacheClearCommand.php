<?php

namespace App\Console\Commands;

use Illuminate\Cache\Console\ClearCommand as BaseClearCommand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;

class CacheClearCommand extends BaseClearCommand
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get cache driver configuration
        $cacheDriver = Config::get('cache.default');
        $cacheStore = $this->argument('store') ?: $cacheDriver;
        $storeConfig = Config::get("cache.stores.{$cacheStore}");

        // If using database driver, check if the cache table exists
        if (isset($storeConfig['driver']) && $storeConfig['driver'] === 'database') {
            try {
                $cacheTable = $storeConfig['table'] ?? 'cache';
                
                if (!Schema::hasTable($cacheTable)) {
                    $this->components->warn("Cache table '{$cacheTable}' does not exist!");
                    $this->components->info("Run 'php bsidlify migrate' to create the necessary database tables.");
                    return;
                }
            } catch (Exception $e) {
                $this->components->warn("Could not check database tables: " . $e->getMessage());
                $this->components->info("Run 'php bsidlify migrate' to ensure all database tables are created.");
                return;
            }
        }

        // If we get here, proceed with the original command
        $this->laravel['events']->dispatch(
            'cache:clearing', [$this->argument('store'), $this->tags()]
        );

        try {
            $successful = $this->cache()->flush();
            $this->flushFacades();

            if (! $successful) {
                return $this->components->error('Failed to clear cache. Make sure you have the appropriate permissions.');
            }

            $this->laravel['events']->dispatch(
                'cache:cleared', [$this->argument('store'), $this->tags()]
            );

            $this->components->info('Application cache cleared successfully.');
        } catch (Exception $e) {
            $this->components->error('Error clearing cache: ' . $e->getMessage());
            $this->components->info("If this is related to missing database tables, run 'php bsidlify migrate' first.");
        }
    }
} 