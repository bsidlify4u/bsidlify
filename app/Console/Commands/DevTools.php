<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Foundation\SmartConfig;
use App\Http\SmartRouteCache;

class DevTools extends Command
{
    protected $signature = 'dev:tools 
                          {action : Action to perform (stats|clear-cache|analyze|optimize)}
                          {--detailed : Show detailed information}';

    protected $description = 'Development tools for Bsidlify';

    public function handle()
    {
        $action = $this->argument('action');
        $detailed = $this->option('detailed');

        switch ($action) {
            case 'stats':
                $this->showStats($detailed);
                break;
            case 'clear-cache':
                $this->clearCaches();
                break;
            case 'analyze':
                $this->analyzePerformance();
                break;
            case 'optimize':
                $this->optimizeDevelopment();
                break;
        }
    }

    protected function showStats($detailed)
    {
        // Enable query logging if not already enabled
        if (!DB::logging()) {
            DB::enableQueryLog();
        }

        $stats = [
            'Config' => SmartConfig::getStats(),
            'Database' => [
                'total_queries' => count(DB::getQueryLog()),
                'cached_queries' => $this->getCachedQueriesCount(),
            ],
            'Routes' => [
                'cached' => file_exists(app()->getCachedRoutesPath()),
                'total' => count(app('router')->getRoutes()),
            ],
            'Cache' => [
                'driver' => config('cache.default'),
                'supported_tags' => method_exists(Cache::store(), 'tags'),
            ],
        ];

        if ($detailed) {
            $stats['Performance'] = [
                'memory_current' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                'uptime' => $this->formatUptime(microtime(true) - BSIDLIFY_START),
            ];
            
            if (count(DB::getQueryLog()) > 0) {
                $stats['Latest Queries'] = collect(DB::getQueryLog())
                    ->take(5)
                    ->map(function ($query) {
                        return [
                            'sql' => $query['query'],
                            'time' => $query['time'] . 'ms',
                        ];
                    })
                    ->toArray();
            }
        }

        $this->table(['Metric', 'Value'], $this->formatStats($stats));
    }

    protected function clearCaches()
    {
        $this->clearAllCaches();
        $this->info('All development caches cleared!');
        
        // Show current cache status
        $this->table(['Cache Type', 'Status'], [
            ['Config Cache', file_exists(app()->getCachedConfigPath()) ? 'Exists' : 'Cleared'],
            ['Route Cache', file_exists(app()->getCachedRoutesPath()) ? 'Exists' : 'Cleared'],
            ['View Cache', is_dir(storage_path('framework/views')) ? 'Exists' : 'Cleared'],
            ['Application Cache', $this->checkCacheStatus()],
        ]);
    }

    protected function analyzePerformance()
    {
        $metrics = app('performance.monitor')->getMetrics();
        $this->table(['Section', 'Duration (ms)', 'Memory (KB)'], $this->formatMetrics($metrics));
    }

    protected function optimizeDevelopment()
    {
        // Implement development environment optimizations
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        
        $this->info('Development environment optimized!');
    }

    protected function formatStats($stats, $prefix = '')
    {
        $rows = [];
        foreach ($stats as $key => $value) {
            if (is_array($value)) {
                $rows = array_merge($rows, $this->formatStats($value, $prefix . $key . ' > '));
            } else {
                $rows[] = [$prefix . $key, $value];
            }
        }
        return $rows;
    }

    protected function formatMetrics($metrics)
    {
        return collect($metrics)->map(function ($metric, $name) {
            return [
                $name,
                number_format($metric['duration'], 2),
                number_format($metric['memory'] / 1024, 2),
            ];
        })->toArray();
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = floor($seconds % 60);
        
        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($seconds > 0) $parts[] = $seconds . 's';
        
        return implode(' ', $parts);
    }

    protected function getCachedQueriesCount()
    {
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                return Cache::tags(['queries'])->count();
            }
            // For non-taggable cache stores, try to get an estimate
            $keys = Cache::get('query_keys', []);
            return count($keys);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    protected function clearAllCaches()
    {
        $results = [];
        
        try {
            $this->call('cache:clear');
            $results[] = ['Application Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Application Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            $this->call('config:clear');
            $results[] = ['Config Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Config Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            $this->call('route:clear');
            $results[] = ['Route Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Route Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            $this->call('view:clear');
            $results[] = ['View Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['View Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            SmartConfig::clear();
            $results[] = ['Smart Config Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Smart Config Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            SmartRouteCache::clear();
            $results[] = ['Smart Route Cache', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Smart Route Cache', '✗ Failed: ' . $e->getMessage()];
        }

        try {
            $this->clearCacheFiles();
            $results[] = ['Cache Files', '✓ Cleared'];
        } catch (\Exception $e) {
            $results[] = ['Cache Files', '✗ Failed: ' . $e->getMessage()];
        }

        // Show results table
        $this->table(['Cache Type', 'Status'], $results);
    }

    protected function clearCacheFiles()
    {
        $files = glob(storage_path('framework/cache/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function checkCacheStatus(): string
    {
        try {
            $store = Cache::getStore();
            
            // Check store type and handle accordingly
            if ($store instanceof \Illuminate\Cache\DatabaseStore) {
                // For database driver, check cache table
                return \DB::table('cache')->count() > 0 ? 'Has Items' : 'Empty';
            } 
            elseif ($store instanceof \Illuminate\Cache\RedisStore) {
                // For Redis, check if any keys exist
                $keys = Cache::getRedis()->keys('*');
                return !empty($keys) ? 'Has Items' : 'Empty';
            }
            elseif ($store instanceof \Illuminate\Cache\FileStore) {
                // For file driver, check if cache directory has files
                $files = glob(storage_path('framework/cache/*'));
                return !empty($files) ? 'Has Items' : 'Empty';
            }
            elseif (method_exists($store, 'count')) {
                // For drivers that support counting
                return $store->count() > 0 ? 'Has Items' : 'Empty';
            }
            
            // Default status for unknown drivers
            return 'Cleared';
        } catch (\Exception $e) {
            return 'Status Unknown';
        }
    }
}
