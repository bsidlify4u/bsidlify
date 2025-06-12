<?php

namespace App\Template;

use League\Plates\Engine;
use Illuminate\Contracts\View\Factory as ViewFactory;

class PlatesEngine implements TemplateEngineInterface
{
    /**
     * The Plates engine instance
     *
     * @var \League\Plates\Engine
     */
    protected $plates;
    
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
     * Create a new Plates engine instance
     *
     * @param array $options
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param string $extension
     */
    public function __construct(
        array $options,
        ViewFactory $viewFactory,
        string $extension = 'plate.php'
    ) {
        $this->plates = new Engine($options['directory'] ?? resource_path('views'), $extension);
        
        // Register folder namespaces
        if (isset($options['folder_namespace'])) {
            $this->plates->addFolder($options['folder_namespace'], $options['directory'] ?? resource_path('views'));
        }
        
        $this->viewFactory = $viewFactory;
        $this->extension = $extension;
        
        // Add some global data and functions if needed
        $this->plates->addData([
            'app' => app(),
            'auth' => auth(),
            'config' => app('config'),
            'session' => session(),
        ]);
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
        $template = str_replace('.', '/', $view);
        
        return $this->plates->render($template, $data);
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
            // Convert dot notation to directory separator
            $template = str_replace('.', '/', $view);
            return $this->plates->exists($template);
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
            $this->plates->addData($key);
            
            return $this->shared;
        }
        
        $this->shared[$key] = $value;
        $this->plates->addData([$key => $value]);
        
        return $value;
    }
    
    /**
     * Get the Plates engine instance
     *
     * @return \League\Plates\Engine
     */
    public function getPlates(): Engine
    {
        return $this->plates;
    }
} 