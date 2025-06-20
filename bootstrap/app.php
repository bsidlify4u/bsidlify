<?php

use App\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        $middleware->alias([
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        // Configure rate limiting with string format
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
