<?php

namespace App\Foundation;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class SmartConfig
{
    protected static $cache = [];
    protected static $hits = [];
    protected static $environment;

    public static function load($key, $default = null)
    {
        // In production, use file-based cache
        if (app()->environment('production')) {
            return static::loadFromCache($key, $default);
        }

        // In development, use memory cache
        return static::loadFromMemory($key, $default);
    }

    protected static function loadFromCache($key, $default)
    {
        $cacheKey = 'config_' . md5($key);
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($key, $default) {
            return config($key, $default);
        });
    }

    protected static function loadFromMemory($key, $default)
    {
        if (!isset(static::$cache[$key])) {
            static::$cache[$key] = config($key, $default);
            static::$hits[$key] = 1;
        } else {
            static::$hits[$key]++;
        }

        // Auto-optimize frequently accessed configs
        if (static::$hits[$key] > 10) {
            static::optimize($key);
        }

        return static::$cache[$key];
    }

    protected static function optimize($key)
    {
        // Store frequently accessed configs in APC if available
        if (function_exists('apcu_store')) {
            apcu_store('bsidlify_config_' . $key, static::$cache[$key], 3600);
        }
    }

    public static function clear()
    {
        static::$cache = [];
        static::$hits = [];
        
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags(['config'])->flush();
            } else {
                Cache::flush();
            }
        } catch (\Exception $e) {
            // If cache clearing fails, at least our memory cache is cleared
            report($e);
        }
    }

    public static function getStats()
    {
        return [
            'memory_cache_size' => count(static::$cache),
            'hits' => static::$hits,
        ];
    }
}
