<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as CollisionTestCommand;

class TestCommand extends CollisionTestCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test
        {--without-tty : Disable output to TTY}
        {--compact : Indicates whether the compact printer should be used}
        {--coverage : Indicates whether code coverage information should be collected}
        {--min= : Indicates the minimum threshold enforcement for code coverage}
        {--p|parallel : Indicates if the tests should run in parallel}
        {--profile : Lists top 10 slowest tests}
        {--recreate-databases : Indicates if the test databases should be re-created}
        {--drop-databases : Indicates if the test databases should be dropped}
        {--without-databases : Indicates if database configuration should be performed}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Bsidlify application tests';
} 