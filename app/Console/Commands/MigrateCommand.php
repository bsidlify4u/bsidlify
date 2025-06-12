<?php

namespace App\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Exception;
use PDOException;
use PDO;

class MigrateCommand extends BaseMigrateCommand
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        // Run pre-migration checks
        if (!$this->runPreMigrationChecks()) {
            return 1;
        }

        try {
            $this->runMigrations();
        } catch (\Throwable $e) {
            if ($this->option('graceful')) {
                $this->components->warn($e->getMessage());
                return 0;
            }

            $this->handleMigrationError($e);
            return 1;
        }

        return 0;
    }

    /**
     * Run checks before executing migrations
     *
     * @return bool
     */
    protected function runPreMigrationChecks()
    {
        // Check 1: Verify database connection
        if (!$this->checkDatabaseConnection()) {
            return false;
        }

        // Check 2: Check for existing tables that might conflict
        if (!$this->checkForTableConflicts()) {
            return false;
        }

        // Check 3: Verify migration directory exists
        if (!$this->checkMigrationDirectory()) {
            return false;
        }

        // All checks passed
        return true;
    }

    /**
     * Check if database connection is working
     *
     * @return bool
     */
    protected function checkDatabaseConnection()
    {
        $connection = $this->option('database') ?: config('database.default');
        $config = config("database.connections.{$connection}");

        if (!$config) {
            $this->components->error("Database connection '{$connection}' not configured!");
            $this->components->info("Please check your .env file and database configuration.");
            return false;
        }

        try {
            DB::connection($connection)->getPdo();
            $this->components->info("Database connection successful.");
            return true;
        } catch (PDOException $e) {
            $this->components->error("Cannot connect to database: " . $e->getMessage());
            
            // Provide specific fallback based on driver
            $driver = $config['driver'] ?? 'unknown';
            
            if ($driver === 'sqlite') {
                $database = $config['database'] ?? '';
                if (!File::exists($database) && $database !== ':memory:') {
                    $this->components->info("SQLite database file not found. It will be created automatically.");
                    return true;
                }
            } elseif (in_array($driver, ['mysql', 'pgsql'])) {
                $this->components->info("Make sure your database server is running and credentials are correct in .env file.");
                $this->components->info("Database: {$config['database']}, Host: {$config['host']}, Username: {$config['username']}");
            }
            
            return false;
        }
    }

    /**
     * Check for existing tables that might conflict with migrations
     *
     * @return bool
     */
    protected function checkForTableConflicts()
    {
        $connection = $this->option('database') ?: config('database.default');
        
        try {
            // Check if the migrations table exists
            if (Schema::connection($connection)->hasTable('migrations')) {
                // Get all migration file names (without extension)
                $migrationFiles = collect(File::glob(database_path('migrations/*.php')))
                    ->map(function ($file) {
                        return basename($file, '.php');
                    });
                
                // Get all migrated migrations
                $ranMigrations = DB::connection($connection)
                    ->table('migrations')
                    ->pluck('migration')
                    ->toArray();
                
                // Check if there are specific paths provided
                $paths = $this->option('path');
                if (!empty($paths)) {
                    $targetMigrations = collect();
                    foreach ($paths as $path) {
                        $targetMigrations = $targetMigrations->merge(
                            collect(File::glob(base_path($path) . '/*.php'))
                                ->map(function ($file) {
                                    return basename($file, '.php');
                                })
                        );
                    }
                    
                    $migrationFiles = $targetMigrations;
                }
                
                // Check for files that aren't in the migrations table
                $pendingMigrations = $migrationFiles->diff($ranMigrations);
                
                if ($pendingMigrations->isEmpty()) {
                    $this->components->info("No pending migrations to run.");
                } else {
                    $this->components->info("Found " . $pendingMigrations->count() . " pending migration(s).");
                }
                
                // Check for potential conflicts (tables that already exist)
                $conflictingTables = [];
                foreach ($pendingMigrations as $migration) {
                    $migrationFile = File::glob(database_path("migrations/*{$migration}.php"))[0] ?? null;
                    if ($migrationFile) {
                        $content = File::get($migrationFile);
                        preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches);
                        
                        if (!empty($matches[1])) {
                            foreach ($matches[1] as $table) {
                                if (Schema::connection($connection)->hasTable($table)) {
                                    $conflictingTables[] = $table;
                                }
                            }
                        }
                    }
                }
                
                if (!empty($conflictingTables)) {
                    $this->components->warn("The following tables already exist but have pending migrations:");
                    foreach ($conflictingTables as $table) {
                        $this->components->warn("- {$table}");
                    }
                    
                    $this->components->info("You have the following options:");
                    $this->components->info("1. Use php bsidlify migrate:fresh to rebuild all tables (data will be lost)");
                    $this->components->info("2. Use php bsidlify migrate:refresh to rebuild specific tables");
                    $this->components->info("3. Manually update the migrations table to mark these migrations as completed");
                    
                    if (!$this->option('force')) {
                        $confirmed = $this->confirm('Do you want to continue anyway?', false);
                        if (!$confirmed) {
                            return false;
                        }
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            $this->components->warn("Could not check for table conflicts: " . $e->getMessage());
            return true; // Continue with migration anyway
        }
    }

    /**
     * Check if migration directory exists
     *
     * @return bool
     */
    protected function checkMigrationDirectory()
    {
        $paths = $this->option('path');
        
        if (empty($paths)) {
            $defaultPath = database_path('migrations');
            
            if (!File::isDirectory($defaultPath)) {
                $this->components->warn("Default migrations directory not found: {$defaultPath}");
                $this->components->info("Creating migrations directory...");
                
                try {
                    File::makeDirectory($defaultPath, 0755, true);
                    $this->components->info("Migrations directory created successfully.");
                } catch (Exception $e) {
                    $this->components->error("Failed to create migrations directory: " . $e->getMessage());
                    return false;
                }
            }
        } else {
            foreach ($paths as $path) {
                $fullPath = base_path($path);
                
                if (!File::isDirectory($fullPath)) {
                    $this->components->warn("Migrations directory not found: {$fullPath}");
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Handle migration errors with detailed feedback
     *
     * @param \Throwable $e
     * @return void
     */
    protected function handleMigrationError(\Throwable $e)
    {
        $this->components->error("Migration failed: " . $e->getMessage());
        
        // Check for common errors and provide helpful messages
        if (str_contains($e->getMessage(), 'already exists')) {
            preg_match('/table "([^"]+)" already exists/', $e->getMessage(), $matches);
            $table = $matches[1] ?? 'unknown';
            
            $this->components->info("Table '{$table}' already exists but the migration is trying to create it.");
            $this->components->info("Possible solutions:");
            $this->components->info("1. Use 'php bsidlify migrate:fresh' to rebuild all tables (data will be lost)");
            $this->components->info("2. Manually edit the migrations table to mark this migration as completed:");
            $this->components->info("   - Run: php bsidlify tinker");
            $this->components->info("   - Then: DB::table('migrations')->insert(['migration' => 'MIGRATION_NAME', 'batch' => 1]);");
        } elseif (str_contains($e->getMessage(), 'no such table')) {
            $this->components->info("A migration is trying to modify a table that doesn't exist.");
            $this->components->info("Make sure you run migrations in the correct order.");
        } elseif (str_contains($e->getMessage(), 'SQLSTATE[42S01]')) {
            $this->components->info("A table is being created multiple times. Check your migrations for duplicates.");
        }
    }
} 