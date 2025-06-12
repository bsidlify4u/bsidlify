<?php

namespace App\Console\Commands\Queue;

use Illuminate\Console\Command;
use App\Queue\QueueOrchestrator;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;

class MonitorCommand extends Command
{
    protected $signature = 'queue:monitor
                          {queues?* : The names of the queues to monitor}
                          {--interval=5 : The interval in seconds between checks}
                          {--metrics : Show detailed metrics}
                          {--auto-scale : Enable auto-scaling of workers}';

    protected $description = 'Monitor queue performance and health';

    protected $orchestrator;

    public function __construct(QueueOrchestrator $orchestrator)
    {
        parent::__construct();
        $this->orchestrator = $orchestrator;
    }

    public function handle()
    {
        $queues = $this->argument('queues') ?: ['default'];
        $interval = $this->option('interval');
        $showMetrics = $this->option('metrics');
        $autoScale = $this->option('auto-scale');

        $this->info('Starting queue monitor...');
        $this->info('Press Ctrl+C to stop');

        while (true) {
            $this->clearScreen();
            
            foreach ($queues as $queue) {
                $metrics = $this->orchestrator->monitorQueue($queue);
                
                $this->displayQueueStatus($queue, $metrics);
                
                if ($showMetrics) {
                    $this->displayDetailedMetrics($queue, $metrics);
                }
                
                if ($autoScale) {
                    $workers = $this->orchestrator->autoScale($queue);
                    $this->info("Auto-scaled {$queue} to {$workers} workers");
                }
            }

            sleep($interval);
        }
    }

    protected function displayQueueStatus(string $queue, array $metrics): void
    {
        $table = new Table($this->output);
        $table->setHeaders(['Queue', 'Size', 'Processed', 'Failed', 'Latency', 'Throughput']);
        $table->addRow([
            $queue,
            $metrics['size'],
            $metrics['processed'],
            $metrics['failed'],
            round($metrics['latency'], 2) . 'ms',
            round($metrics['throughput'], 2) . '/s'
        ]);
        $table->render();
    }

    protected function displayDetailedMetrics(string $queue, array $metrics): void
    {
        $this->line("\nDetailed metrics for queue: {$queue}");
        
        $stats = $this->orchestrator->getMetrics()->get($queue);
        if ($stats) {
            foreach ($stats as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
        }
    }

    protected function clearScreen(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            system('cls');
        } else {
            system('clear');
        }
    }
}
