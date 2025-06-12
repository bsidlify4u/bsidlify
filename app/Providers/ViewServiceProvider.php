<?php

namespace App\Providers;

use Illuminate\View\ViewServiceProvider as LaravelViewServiceProvider;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use App\Template\TemplateManager;

class ViewServiceProvider extends LaravelViewServiceProvider
{
    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        parent::registerViewFinder();
        
        $this->app->singleton('template', function ($app) {
            return new TemplateManager($app, $app['view']);
        });
    }
    
    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        parent::registerFactory();
        
        // Extend the view factory to use our template manager for rendering
        $this->app->resolving('view', function (Factory $view, $app) {
            // We'll use the view factory's original render method when needed,
            // but we'll also add access to our template manager
            $view->macro('template', function () use ($app) {
                return $app['template'];
            });
            
            // Add helper to check which engine will be used for a view
            $view->macro('engineFor', function ($view) use ($app) {
                return $app['template']->forView($view)->getExtension();
            });
        });
    }
} 