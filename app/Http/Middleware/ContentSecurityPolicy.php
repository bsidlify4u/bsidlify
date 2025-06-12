<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:5173; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net http://127.0.0.1:5173; " .
            "font-src 'self' https://fonts.bunny.net; " .
            "img-src 'self' data: https:;"
        );

        return $response;
    }
}
