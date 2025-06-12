<?php

namespace App\Console;

use Illuminate\Support\Facades\Cache;

class CommandCache
{
    protected static $prefix = 'bsidlify_cmd_';
    protected static $ttl = 3600; // 1 hour

    public static function remember(string $command, callable $callback)
    {
        $key = self::$prefix . md5($command);
        return Cache::remember($key, self::$ttl, $callback);
    }

    public static function forget(string $command)
    {
        Cache::forget(self::$prefix . md5($command));
    }

    public static function clear()
    {
        $keys = Cache::get(self::$prefix . '*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
