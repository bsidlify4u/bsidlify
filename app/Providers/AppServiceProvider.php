<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Facades\Bsidlify;
use App\Http\SmartRouteCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the Bsidlify facade as an alias for Artisan
        $this->app->booting(function() {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Bsidlify', Bsidlify::class);
        });

        // Register performance monitoring services
        if (!$this->app->environment('production')) {
            $this->app->register(DevelopmentToolsProvider::class);
        }

        // Register command caching
        $this->app->singleton('command.cache', function ($app) {
            return new \App\Console\CommandCache;
        });
        
        // Register custom view and template service providers
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(TemplateServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enable route caching with SmartRouteCache
        Route::matched(function ($route) {
            SmartRouteCache::cacheRoute($route);
        });

        // Add performance middleware to web group
        $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\PerformanceOptimizer::class);
        
        // Enable query caching in production
        if ($this->app->environment('production')) {
            \DB::connection()->enableQueryLog();
            \DB::listen(function ($query) {
                // Cache frequently executed queries
                $key = 'query_' . md5($query->sql . implode('', $query->bindings));
                if (\Cache::has($key)) {
                    \Cache::increment($key . '_hits');
                } else {
                    \Cache::put($key, 1, now()->addHours(1));
                }
            });
        }
    }
}
