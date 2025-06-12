<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Template\TemplateEngineInterface driver(string $driver = null)
 * @method static string render(string $view, array $data = [], array $mergeData = [])
 * @method static \App\Template\TemplateEngineInterface forView(string $view)
 * @method static \App\Template\TemplateEngineInterface forPath(string $path)
 * 
 * @see \App\Template\TemplateManager
 */
class Template extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'template';
    }
} 