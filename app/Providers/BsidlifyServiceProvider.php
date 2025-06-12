<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Queue\QueueOrchestrator;
use App\Internationalization\LocalizationManager;

class BsidlifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerQueueOrchestrator();
        $this->registerLocalizationManager();
        $this->registerApiDocumentation();
    }

    protected function registerQueueOrchestrator(): void
    {
        $this->app->singleton('queue.orchestrator', function ($app) {
            return new QueueOrchestrator($app['queue']);
        });
    }

    protected function registerLocalizationManager(): void
    {
        $this->app->singleton('localization', function ($app) {
            return new LocalizationManager($app);
        });

        // Register the localization middleware
        $this->app['router']->aliasMiddleware('localize', \App\Http\Middleware\LocalizationMiddleware::class);

        // Add middleware to web group
        $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\LocalizationMiddleware::class);
    }

    protected function registerApiDocumentation(): void
    {
        $this->app->singleton('api.documentation', function ($app) {
            return new ApiDocumentationGenerator($app);
        });
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('api.docs', \App\Http\Middleware\ApiDocumentationGenerator::class);

        // Register testing macros
        if ($this->app->environment('testing')) {
            $this->bootTestingMacros();
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ApiDocs\GenerateCommand::class,
                \App\Console\Commands\Queue\MonitorCommand::class,
                \App\Console\Commands\Localization\SyncCommand::class,
                \App\Console\Commands\DocsCommand::class,
            ]);
        }
    }

    protected function bootTestingMacros(): void
    {
        \Illuminate\Testing\TestResponse::mixin(new \App\Testing\AdvancedAssertions);
    }
}
