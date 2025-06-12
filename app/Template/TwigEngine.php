<?php

namespace App\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\View\Factory as ViewFactory;

class TwigEngine implements TemplateEngineInterface
{
    /**
     * The Twig environment instance
     *
     * @var \Twig\Environment
     */
    protected $twig;
    
    /**
     * The view finder instance
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;
    
    /**
     * Shared variables
     *
     * @var array
     */
    protected $shared = [];
    
    /**
     * The file extension for this template type
     *
     * @var string
     */
    protected $extension;
    
    /**
     * Create a new Twig engine instance
     *
     * @param array $viewPaths
     * @param array $options
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param string $extension
     */
    public function __construct(
        array $viewPaths,
        array $options,
        ViewFactory $viewFactory,
        string $extension = 'twig'
    ) {
        // Create Twig cache directory if it doesn't exist
        if (isset($options['cache']) && !File::exists($options['cache'])) {
            File::makeDirectory($options['cache'], 0755, true);
        }
        
        $loader = new FilesystemLoader($viewPaths);
        $this->twig = new Environment($loader, $options);
        $this->viewFactory = $viewFactory;
        $this->extension = $extension;
        
        // Add global helpers and facades here if needed
        $this->twig->addGlobal('app', app());
        $this->twig->addGlobal('auth', auth());
        $this->twig->addGlobal('config', app('config'));
        $this->twig->addGlobal('session', session());
    }
    
    /**
     * Render a template with the given data
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return string
     */
    public function render(string $view, array $data = [], array $mergeData = []): string
    {
        $data = array_merge($mergeData, $this->shared, $data);
        
        // Convert dot notation to directory separator
        $path = str_replace('.', '/', $view) . '.' . $this->extension;
        
        return $this->twig->render($path, $data);
    }
    
    /**
     * Check if a template exists
     *
     * @param string $view
     * @return bool
     */
    public function exists(string $view): bool
    {
        try {
            $path = str_replace('.', '/', $view) . '.' . $this->extension;
            return $this->twig->getLoader()->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get the file extension this engine handles
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }
    
    /**
     * Share data across all templates
     *
     * @param string|array $key
     * @param mixed|null $value
     * @return mixed
     */
    public function share(string|array $key, mixed $value = null): mixed
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
            
            return $this->shared;
        }
        
        $this->shared[$key] = $value;
        
        return $value;
    }
    
    /**
     * Get the Twig environment instance
     *
     * @return \Twig\Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }
} 