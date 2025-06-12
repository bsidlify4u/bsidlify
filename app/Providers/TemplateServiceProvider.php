<?php

namespace App\Providers;

use App\Template\TemplateManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\View\Factory as ViewFactory;
use League\Plates\Engine as PlatesEngine;
use Twig\Environment as TwigEnvironment;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('template', function ($app) {
            return new TemplateManager($app, $app['view']);
        });
        
        // Create directories for template engines if they don't exist
        $this->createCacheDirectories();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Twig extensions if needed
        if (class_exists(TwigEnvironment::class)) {
            $this->bootTwigExtensions();
        }
        
        // Register Plates extensions if needed
        if (class_exists(PlatesEngine::class)) {
            $this->bootPlatesExtensions();
        }
        
        // Ensure .env knows about template engine selection
        if (!$this->app->runningInConsole()) {
            $this->app['config']->set(
                'view.default_engine', 
                env('VIEW_ENGINE', $this->app['config']->get('view.default_engine', 'blade'))
            );
        }
    }
    
    /**
     * Create cache directories for template engines
     */
    protected function createCacheDirectories(): void
    {
        $twigCachePath = $this->app['config']->get(
            'view.engines.twig.options.cache',
            storage_path('framework/twig')
        );
        
        if (!file_exists($twigCachePath)) {
            mkdir($twigCachePath, 0755, true);
        }
    }
    
    /**
     * Boot Twig extensions
     */
    protected function bootTwigExtensions(): void
    {
        // Register Twig extensions here if needed
        $this->app->resolving('template.driver.twig', function ($twig) {
            // Add extensions when the Twig driver is resolved
        });
    }
    
    /**
     * Boot Plates extensions
     */
    protected function bootPlatesExtensions(): void
    {
        // Register Plates extensions here if needed
        $this->app->resolving('template.driver.plates', function ($plates) {
            // Add extensions when the Plates driver is resolved
        });
    }
} 