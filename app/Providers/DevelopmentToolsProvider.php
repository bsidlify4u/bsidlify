<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\QueryExecuted;

class DevelopmentToolsProvider extends ServiceProvider
{
    public function register()
    {
        if (!$this->app->environment('production')) {
            $this->registerQueryLogger();
            $this->registerPerformanceMonitor();
        }
    }

    public function boot()
    {
        if (!$this->app->environment('production')) {
            $this->bootDebugTools();
        }
    }

    protected function registerQueryLogger()
    {
        Event::listen(QueryExecuted::class, function ($query) {
            $sql = str_replace(['?'], array_map(function ($binding) {
                return is_numeric($binding) ? $binding : "'{$binding}'";
            }, $query->bindings), $query->sql);
            
            $this->logQuery([
                'sql' => $sql,
                'time' => $query->time,
                'connection' => $query->connectionName
            ]);
        });
    }

    protected function registerPerformanceMonitor()
    {
        $this->app->singleton('performance.monitor', function () {
            return new class {
                protected $metrics = [];
                
                public function start($name)
                {
                    $this->metrics[$name] = [
                        'start' => microtime(true),
                        'memory_start' => memory_get_usage()
                    ];
                }
                
                public function end($name)
                {
                    if (isset($this->metrics[$name])) {
                        $this->metrics[$name]['end'] = microtime(true);
                        $this->metrics[$name]['memory_end'] = memory_get_usage();
                        $this->metrics[$name]['duration'] = 
                            ($this->metrics[$name]['end'] - $this->metrics[$name]['start']) * 1000;
                        $this->metrics[$name]['memory'] = 
                            $this->metrics[$name]['memory_end'] - $this->metrics[$name]['memory_start'];
                    }
                }
                
                public function getMetrics()
                {
                    return $this->metrics;
                }
            };
        });
    }

    protected function bootDebugTools()
    {
        if (request()->hasHeader('X-Debug-Token')) {
            $this->app['performance.monitor']->start('request');
            
            register_shutdown_function(function () {
                $this->app['performance.monitor']->end('request');
                $metrics = $this->app['performance.monitor']->getMetrics();
                
                // Store metrics for the debug toolbar
                file_put_contents(
                    storage_path('logs/performance.log'),
                    json_encode($metrics) . PHP_EOL,
                    FILE_APPEND
                );
            });
        }
    }

    protected function logQuery($query)
    {
        $logPath = storage_path('logs/queries.log');
        $content = date('Y-m-d H:i:s') . ' - ' . json_encode($query) . PHP_EOL;
        file_put_contents($logPath, $content, FILE_APPEND);
    }
}
