<?php

namespace App\Console\Commands;

use Illuminate\Database\Console\DbCommand as LaravelDbCommand;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[AsCommand(name: 'db')]
class DbCommand extends LaravelDbCommand
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->getConnection();

        if (! isset($connection['host']) && $connection['driver'] !== 'sqlite') {
            $this->components->error('No host specified for this database connection.');
            $this->line('  Use the <options=bold>[--read]</> and <options=bold>[--write]</> options to specify a read or write connection.');
            $this->newLine();

            return Command::FAILURE;
        }

        try {
            $process = new Process(
                array_merge([$command = $this->getCommand($connection)], $this->commandArguments($connection)),
                null,
                $this->commandEnvironment($connection)
            );
            
            $process->setTimeout(null);
            
            // Only use TTY if it's supported and not explicitly disabled
            if (!getenv('DISABLE_TTY')) {
                $process->setTty(Process::isTtySupported());
            }
            
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        } catch (ProcessFailedException $e) {
            throw_unless($e->getProcess()->getExitCode() === 127, $e);

            $this->error("{$command} not found in path.");

            return Command::FAILURE;
        }

        return 0;
    }
} 