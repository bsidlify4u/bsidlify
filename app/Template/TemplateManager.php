<?php

namespace App\Template;

use Illuminate\Support\Str;
use Illuminate\View\Factory;
use InvalidArgumentException;
use Illuminate\Support\Manager;
use Illuminate\Contracts\View\Factory as ViewFactory;

class TemplateManager extends Manager
{
    /**
     * The view factory instance
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;
    
    /**
     * Create a new template manager instance
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     */
    public function __construct($app, ViewFactory $viewFactory)
    {
        parent::__construct($app);
        $this->viewFactory = $viewFactory;
    }
    
    /**
     * Get the default driver name
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('view.default_engine', 'blade');
    }
    
    /**
     * Create the Blade template engine driver
     *
     * @return \App\Template\BladeEngine
     */
    protected function createBladeDriver()
    {
        $extension = $this->config->get('view.engines.blade.extension', 'blade.php');
        
        return new BladeEngine($this->viewFactory, $extension);
    }
    
    /**
     * Create the Twig template engine driver
     *
     * @return \App\Template\TwigEngine
     */
    protected function createTwigDriver()
    {
        $viewPaths = $this->config->get('view.paths', [resource_path('views')]);
        $options = $this->config->get('view.engines.twig.options', []);
        $extension = $this->config->get('view.engines.twig.extension', 'twig');
        
        return new TwigEngine($viewPaths, $options, $this->viewFactory, $extension);
    }
    
    /**
     * Create the Plates template engine driver
     *
     * @return \App\Template\PlatesEngine
     */
    protected function createPlatesDriver()
    {
        $options = $this->config->get('view.engines.plates.options', []);
        $extension = $this->config->get('view.engines.plates.extension', 'plate.php');
        
        return new PlatesEngine($options, $this->viewFactory, $extension);
    }
    
    /**
     * Get the template engine based on the file extension
     *
     * @param string $path
     * @return \App\Template\TemplateEngineInterface
     * 
     * @throws \InvalidArgumentException
     */
    public function forPath(string $path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        if ($extension === 'php') {
            $filename = pathinfo($path, PATHINFO_FILENAME);
            $parts = explode('.', $filename);
            $lastPart = end($parts);
            
            if ($lastPart === 'blade') {
                return $this->driver('blade');
            }
            
            if ($lastPart === 'plate') {
                return $this->driver('plates');
            }
        } elseif ($extension === 'twig') {
            return $this->driver('twig');
        }
        
        return $this->driver();
    }
    
    /**
     * Get the template engine for the given view
     *
     * @param string $view
     * @return \App\Template\TemplateEngineInterface
     * 
     * @throws \InvalidArgumentException
     */
    public function forView(string $view)
    {
        // Convert dot notation to directory separator for path checking
        $path = str_replace('.', '/', $view);
        
        // Check for each template engine
        foreach (['blade', 'twig', 'plates'] as $engine) {
            $driver = $this->driver($engine);
            $fullPath = $path . '.' . $driver->getExtension();
            
            foreach ($this->config->get('view.paths') as $viewPath) {
                if (file_exists($viewPath . '/' . $fullPath)) {
                    return $driver;
                }
            }
        }
        
        return $this->driver();
    }
    
    /**
     * Render a template with the appropriate engine
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return string
     */
    public function render(string $view, array $data = [], array $mergeData = [])
    {
        return $this->forView($view)->render($view, $data, $mergeData);
    }
} 