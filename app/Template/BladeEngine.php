<?php

namespace App\Template;

use Illuminate\View\Factory;

class BladeEngine implements TemplateEngineInterface
{
    /**
     * The Blade view factory instance
     *
     * @var \Illuminate\View\Factory
     */
    protected $factory;
    
    /**
     * The file extension for this template type
     *
     * @var string
     */
    protected $extension;
    
    /**
     * Create a new Blade engine instance
     *
     * @param \Illuminate\View\Factory $factory
     * @param string $extension
     */
    public function __construct(Factory $factory, string $extension = 'blade.php')
    {
        $this->factory = $factory;
        $this->extension = $extension;
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
        return $this->factory->make($view, $data, $mergeData)->render();
    }
    
    /**
     * Check if a template exists
     *
     * @param string $view
     * @return bool
     */
    public function exists(string $view): bool
    {
        return $this->factory->exists($view);
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
        return $this->factory->share($key, $value);
    }
} 