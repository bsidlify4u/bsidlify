# Cache

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Cache Usage](#cache-usage)
  - [Obtaining A Cache Instance](#obtaining-a-cache-instance)
  - [Retrieving Items From The Cache](#retrieving-items-from-the-cache)
  - [Storing Items In The Cache](#storing-items-in-the-cache)
  - [Removing Items From The Cache](#removing-items-from-the-cache)
  - [The Cache Helper](#the-cache-helper)
- [Cache Tags](#cache-tags)
- [Atomic Locks](#atomic-locks)
- [Enhanced Cache Features](#enhanced-cache-features)
  - [Improved Cache Invalidation](#improved-cache-invalidation)
  - [Cache Monitoring](#cache-monitoring)

<a name="introduction"></a>
## Introduction

Bsidlify provides a unified API for various caching systems. The cache configuration is located at `config/cache.php`. In this file, you may specify which cache driver you would like to be used by default throughout your application. Bsidlify supports popular caching backends like Memcached, Redis, DynamoDB, and database out of the box.

Bsidlify's cache system extends Laravel's capabilities with improved cache invalidation strategies, better monitoring, and enhanced performance metrics.

<a name="configuration"></a>
## Configuration

The cache configuration file is located at `config/cache.php`. In this file, you may specify which cache driver you would like to use by default throughout your application. Bsidlify supports popular caching backends like Memcached, Redis, DynamoDB, and relational databases out of the box. In addition, a file-based cache driver is available, while `array` and "null" cache drivers provide convenient cache backends for your tests.

The cache configuration file also contains various other options, which are documented within the file, so make sure to read through these options. By default, Bsidlify is configured to use the `file` cache driver, which stores serialized, cached objects in the filesystem. For larger applications, it is recommended that you use a more robust driver such as Memcached or Redis.

### Driver Prerequisites

#### Database

When using the `database` cache driver, you will need to create a table to contain the cache items. You'll find an example `Schema` declaration for the table below:

```php
Schema::create('cache', function (Blueprint $table) {
    $table->string('key')->unique();
    $table->text('value');
    $table->integer('expiration');
});

Schema::create('cache_locks', function (Blueprint $table) {
    $table->string('key')->primary();
    $table->string('owner');
    $table->integer('expiration');
});
```

#### Memcached

Using the Memcached driver requires the [Memcached PECL package](https://pecl.php.net/package/memcached) to be installed. You may list all of your Memcached servers in the `config/cache.php` configuration file:

```php
'memcached' => [
    [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 100
    ],
],
```

#### Redis

Before using a Redis cache with Bsidlify, you will need to either install the PhpRedis PHP extension via PECL or install the `predis/predis` package (~1.0) via Composer.

For more information on configuring Redis, consult the [Bsidlify Redis documentation](../database/redis.md).

<a name="cache-usage"></a>
## Cache Usage

<a name="obtaining-a-cache-instance"></a>
### Obtaining A Cache Instance

To obtain a cache store instance, you may use the `Cache` facade, which is what we will use throughout this documentation. The `Cache` facade provides convenient, terse access to the underlying implementations of the Bsidlify cache contracts:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Show a list of all users of the application.
     */
    public function index()
    {
        $value = Cache::get('key');

        //
    }
}
```

<a name="retrieving-items-from-the-cache"></a>
### Retrieving Items From The Cache

The `get` method on the `Cache` facade is used to retrieve items from the cache. If the item does not exist in the cache, `null` will be returned. If you wish, you may pass a second argument to the `get` method specifying the default value you wish to be returned if the item doesn't exist:

```php
$value = Cache::get('key');

$value = Cache::get('key', 'default');
```

You may even pass a closure as the default value. The result of the closure will be returned if the specified item does not exist in the cache. Passing a closure allows you to defer the retrieval of default values from a database or other external service:

```php
$value = Cache::get('key', function () {
    return DB::table(...)->get();
});
```

#### Checking For Item Existence

The `has` method may be used to determine if an item exists in the cache. This method will return `false` if the value is `null` or `false`:

```php
if (Cache::has('key')) {
    //
}
```

#### Incrementing / Decrementing Values

The `increment` and `decrement` methods may be used to adjust the value of integer items in the cache. Both of these methods accept an optional second argument indicating the amount by which to increment or decrement the item's value:

```php
Cache::increment('key');
Cache::increment('key', $amount);
Cache::decrement('key');
Cache::decrement('key', $amount);
```

#### Retrieve & Store

Sometimes you may wish to retrieve an item from the cache, but also store a default value if the requested item doesn't exist. For example, you may wish to retrieve all users from the cache or, if they don't exist, retrieve them from the database and add them to the cache. You may do this using the `remember` method:

```php
$value = Cache::remember('users', $seconds, function () {
    return DB::table('users')->get();
});
```

If the item does not exist in the cache, the closure passed to the `remember` method will be executed and its result will be placed in the cache.

You may use the `rememberForever` method to retrieve an item from the cache or store it forever if it doesn't exist:

```php
$value = Cache::rememberForever('users', function () {
    return DB::table('users')->get();
});
```

<a name="storing-items-in-the-cache"></a>
### Storing Items In The Cache

You may use the `put` method on the `Cache` facade to store items in the cache:

```php
Cache::put('key', 'value', $seconds);
```

If the storage time is not passed to the `put` method, the item will be stored indefinitely:

```php
Cache::put('key', 'value');
```

Instead of passing the number of seconds as an integer, you may also pass a `DateTime` instance representing the expiration time of the cached item:

```php
Cache::put('key', 'value', now()->addMinutes(10));
```

#### Store If Not Present

The `add` method will only add the item to the cache if it does not already exist in the cache store. The method will return `true` if the item is actually added to the cache. Otherwise, the method will return `false`. The `add` method is an atomic operation:

```php
Cache::add('key', 'value', $seconds);
```

<a name="removing-items-from-the-cache"></a>
### Removing Items From The Cache

You may remove items from the cache using the `forget` method:

```php
Cache::forget('key');
```

You may also remove items by providing a zero or negative TTL:

```php
Cache::put('key', 'value', 0);
Cache::put('key', 'value', -5);
```

You may clear the entire cache using the `flush` method:

```php
Cache::flush();
```

> **Warning**  
> Flushing the cache does not respect the cache prefix and will remove all entries from the cache. Consider this carefully when clearing a cache which is shared by other applications.

<a name="the-cache-helper"></a>
### The Cache Helper

In addition to using the `Cache` facade, you may also use the global `cache` function to retrieve and store data via the cache. When the `cache` function is called with a single, string argument, it will return the value of the given key:

```php
$value = cache('key');
```

If you provide an array of key / value pairs and an expiration time to the function, it will store values in the cache for the specified duration:

```php
cache(['key' => 'value'], $seconds);

cache(['key' => 'value'], now()->addMinutes(10));
```

When the `cache` function is called without any arguments, it returns an instance of the `Illuminate\Contracts\Cache\Factory` implementation, allowing you to call other caching methods:

```php
cache()->remember('users', $seconds, function () {
    return DB::table('users')->get();
});

cache()->forget('key');
```

<a name="cache-tags"></a>
## Cache Tags

> **Warning**  
> Cache tags are not supported when using the `file`, `dynamodb`, or `database` cache drivers. Furthermore, when using multiple tags with caches that are stored "forever", performance will be best with a driver such as `memcached`, which automatically purges stale records.

<a name="storing-tagged-cache-items"></a>
### Storing Tagged Cache Items

Cache tags allow you to tag related items in the cache and then flush all cached values that have been assigned a given tag. You may access a tagged cache by passing in an ordered array of tag names. For example, let's access a tagged cache and `put` a value into the cache:

```php
Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
```

<a name="accessing-tagged-cache-items"></a>
### Accessing Tagged Cache Items

To retrieve a tagged cache item, pass the same ordered list of tags to the `tags` method and then call the `get` method with the key you wish to retrieve:

```php
$john = Cache::tags(['people', 'artists'])->get('John');
$anne = Cache::tags(['people', 'authors'])->get('Anne');
```

<a name="removing-tagged-cache-items"></a>
### Removing Tagged Cache Items

You may flush all items that are assigned a tag or list of tags. For example, this statement would remove all caches tagged with either `people`, `authors`, or both. So, both `Anne` and `John` would be removed from the cache:

```php
Cache::tags(['people', 'authors'])->flush();
```

In contrast, this statement would remove only cached values tagged with `authors`, so `Anne` would be removed, but not `John`:

```php
Cache::tags('authors')->flush();
```

<a name="atomic-locks"></a>
## Atomic Locks

Bsidlify's atomic lock feature provides a simple abstraction layer around various lock drivers, such as Redis and the database. Atomic locks allow for pessimistic locking to prevent race conditions in your application.

For example, let's imagine your application has a process that takes a long time to complete and you want to ensure that only one instance of this process can run at a time. You could do this by obtaining an atomic lock before starting the process:

```php
if ($lock = Cache::lock('processing', 120)) {
    try {
        // Process that takes a long time...
    } finally {
        $lock->release();
    }
}
```

<a name="enhanced-cache-features"></a>
## Enhanced Cache Features

Bsidlify extends Laravel's cache capabilities with additional features.

<a name="improved-cache-invalidation"></a>
### Improved Cache Invalidation

Bsidlify includes an enhanced cache invalidation system that allows for more granular control over cache invalidation:

```php
// Invalidate cache based on model events
Cache::invalidateOn(User::class, function ($event, $user) {
    return $event === 'updated' && $user->isDirty('role');
});

// Invalidate cache with patterns
Cache::invalidatePattern('users:*');

// Invalidate cache with dependencies
Cache::put('user-list', $users, 3600, ['dependencies' => ['users', 'roles']]);
Cache::invalidateDependency('users'); // Invalidates all caches with 'users' dependency
```

<a name="cache-monitoring"></a>
### Cache Monitoring

Bsidlify provides built-in cache monitoring tools to help you understand how your cache is performing:

```php
// Get cache statistics
$stats = Cache::stats();

// Monitor cache hit/miss rates
$hitRate = Cache::hitRate();

// Get cache size information
$size = Cache::size();

// Get most frequently accessed cache keys
$topKeys = Cache::topKeys(10);
```

These enhanced features make Bsidlify's cache system more powerful and easier to manage than standard Laravel caching.
