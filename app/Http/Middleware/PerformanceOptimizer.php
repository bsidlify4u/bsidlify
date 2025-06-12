<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PerformanceOptimizer
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if (!$request->ajax() && !$request->wantsJson()) {
            $content = $response->getContent();
            
            // Minify HTML in production
            if (!config('app.debug')) {
                $content = $this->minifyHtml($content);
            }
            
            // Add preload headers for critical assets
            $response->headers->set('Link', $this->generatePreloadHeaders());
            
            $response->setContent($content);
        }
        
        // Add performance headers
        $response->headers->set('X-Server-Timing', $this->getServerTiming());
        
        return $response;
    }
    
    protected function minifyHtml($content)
    {
        // Basic HTML minification
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        ];
        
        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];
        
        return preg_replace($search, $replace, $content);
    }
    
    protected function generatePreloadHeaders()
    {
        $preloads = [];
        
        // Add your critical assets here
        $criticalAssets = [
            '/css/app.css' => 'style',
            '/js/app.js' => 'script',
        ];
        
        foreach ($criticalAssets as $asset => $type) {
            $preloads[] = "<{$asset}>; rel=preload; as={$type}";
        }
        
        return implode(', ', $preloads);
    }
    
    protected function getServerTiming()
    {
        $metrics = [
            'total' => BSIDLIFY_START ? (microtime(true) - BSIDLIFY_START) * 1000 : 0,
        ];
        
        $timing = [];
        foreach ($metrics as $metric => $value) {
            $timing[] = "{$metric};dur={$value}";
        }
        
        return implode(', ', $timing);
    }
}
