<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Broadcasting\BroadcastManager;

class BroadcastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('broadcast.manager', function ($app) {
            return new BroadcastManager($app);
        });

        $this->app->singleton('broadcast', function ($app) {
            return $app['broadcast.manager']->driver();
        });
    }

    public function boot(): void
    {
        // Register broadcast channels
        require base_path('routes/channels.php');
    }
}
