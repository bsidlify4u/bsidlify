<?php

namespace App\Http;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SmartRouteCache
{
    protected static $prefix = 'bsidlify_route_';
    protected static $ttl = 3600; // 1 hour

    public static function cacheRoute($event)
    {
        $route = $event->route;
        $key = self::$prefix . md5($route->uri());
        
        return Cache::remember($key, self::$ttl, function () use ($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getAction(),
                'middleware' => $route->middleware(),
            ];
        });
    }

    public static function getCached($uri)
    {
        return Cache::get(self::$prefix . md5($uri));
    }

    public static function clear()
    {
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags(['routes'])->flush();
            } else {
                // For non-taggable stores, we'll clear keys with our prefix
                $keys = Cache::get(self::$prefix . '*');
                if (is_array($keys)) {
                    foreach ($keys as $key) {
                        Cache::forget($key);
                    }
                }
                // Fallback to clearing all cache if we can't find specific keys
                Cache::flush();
            }
        } catch (\Exception $e) {
            report($e);
            // Attempt to clear all cache as last resort
            try {
                Cache::flush();
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
}
