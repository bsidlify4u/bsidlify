<?php

namespace App\Console;

use App\Console\Commands\CacheClearCommand;
use App\Console\Commands\DbCommand;
use App\Console\Commands\FallbackCommand;
use App\Console\Commands\KeyGenerateCommand;
use App\Console\Commands\MigrateCommand;
use App\Console\Commands\PackageDiscoverCommand;
use App\Console\Commands\TestCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Bsidlify commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FallbackCommand::class,
        DbCommand::class,
        CacheClearCommand::class,
        KeyGenerateCommand::class,
        MigrateCommand::class,
        PackageDiscoverCommand::class,
        TestCommand::class,
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 