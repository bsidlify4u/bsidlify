<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try to get locale from different sources
        $locale = $this->getLocaleFromSources($request);
        
        if ($locale && $this->isValidLocale($locale)) {
            App::setLocale($locale);
            
            // Store locale in session if it's a web request
            if (!$request->expectsJson()) {
                session()->put('locale', $locale);
            }
        }

        $response = $next($request);

        // Add Content-Language header
        if (!$response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', App::getLocale());
        }

        return $response;
    }

    /**
     * Get locale from various sources in order of priority
     */
    protected function getLocaleFromSources(Request $request): ?string
    {
        return $request->query('locale') ??                    // URL query parameter
               $request->header('Accept-Language') ??          // Header
               session('locale') ??                           // Session
               config('localization.fallback_locale');        // Default fallback
    }

    /**
     * Check if the locale is valid
     */
    protected function isValidLocale(string $locale): bool
    {
        $availableLocales = array_keys(config('localization.available_locales', []));
        return in_array($locale, $availableLocales);
    }
}
