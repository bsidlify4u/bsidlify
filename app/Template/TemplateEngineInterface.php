<?php

namespace App\Template;

interface TemplateEngineInterface
{
    /**
     * Render a template with the given data
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return string
     */
    public function render(string $view, array $data = [], array $mergeData = []): string;
    
    /**
     * Check if a template exists
     *
     * @param string $view
     * @return bool
     */
    public function exists(string $view): bool;
    
    /**
     * Get the file extension this engine handles
     *
     * @return string
     */
    public function getExtension(): string;
    
    /**
     * Share data across all templates
     *
     * @param string|array $key
     * @param mixed|null $value
     * @return mixed
     */
    public function share(string|array $key, mixed $value = null): mixed;
} 