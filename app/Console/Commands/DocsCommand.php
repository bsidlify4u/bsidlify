<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs 
                            {page? : The documentation page to display}
                            {section? : The section within the page to display}
                            {--search= : Search for specific content in the documentation}
                            {--list : List all available documentation pages}
                            {--categories : Show documentation categories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Access the Bsidlify documentation';

    /**
     * The base path to the documentation files.
     *
     * @var string
     */
    protected $docsPath;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->docsPath = base_path('resources/docs');
        
        // Create docs directory if it doesn't exist
        if (!File::exists($this->docsPath)) {
            File::makeDirectory($this->docsPath, 0755, true);
            $this->generateDefaultDocs();
        }
        
        // Handle search option
        if ($search = $this->option('search')) {
            return $this->searchDocs($search);
        }
        
        // Handle specific page request
        if ($page = $this->argument('page')) {
            return $this->showPage($page, $this->argument('section'));
        }
        
        // Show documentation index
        return $this->showIndex();
    }
    
    /**
     * Show the documentation index.
     *
     * @return int
     */
    protected function showIndex()
    {
        $this->components->info('Bsidlify Documentation');
        $this->components->twoColumnDetail('<fg=green>Version</>', config('app.version', '12.17.0'));
        
        // Get all categories (directories)
        $categories = $this->getDocumentationCategories();
        
        if (empty($categories)) {
            $this->components->warn('No documentation categories found.');
            return Command::FAILURE;
        }
        
        $this->components->info('Documentation Categories:');
        
        foreach ($categories as $category) {
            $categoryName = basename($category);
            $this->components->twoColumnDetail(
                "<fg=green>" . Str::title(str_replace('-', ' ', $categoryName)) . "</>",
                $this->getCategoryDescription($categoryName)
            );
            
            // Get pages in this category
            $pages = $this->getPagesInCategory($categoryName);
            
            if (!empty($pages)) {
                foreach ($pages as $page) {
                    $pageName = basename($page, '.md');
                    $this->components->bulletList(["<fg=yellow>{$categoryName}/{$pageName}</>"]); 
                }
            }
            
            $this->newLine();
        }
        
        $this->components->info('To view a page, run: php bsidlify docs [category]/[page]');
        $this->components->info('To search the documentation, run: php bsidlify docs --search="your query"');
        
        return Command::SUCCESS;
    }
    
    /**
     * Get all documentation categories.
     *
     * @return array
     */
    protected function getDocumentationCategories()
    {
        $directories = File::directories($this->docsPath);
        
        // Sort directories alphabetically
        sort($directories);
        
        return $directories;
    }
    
    /**
     * Get pages in a specific category.
     *
     * @param string $category
     * @return array
     */
    protected function getPagesInCategory($category)
    {
        $categoryPath = $this->docsPath . '/' . $category;
        
        if (!File::exists($categoryPath)) {
            return [];
        }
        
        $pages = File::glob($categoryPath . '/*.md');
        
        // Sort pages alphabetically
        sort($pages);
        
        return $pages;
    }
    
    /**
     * Get a description for a category.
     *
     * @param string $category
     * @return string
     */
    protected function getCategoryDescription($category)
    {
        $descriptions = [
            'prologue' => 'Release notes, upgrade guides, and contribution guidelines',
            'getting-started' => 'Installation, configuration, and basic setup',
            'architecture' => 'Core architecture concepts of the framework',
            'basics' => 'Essential framework features and components',
            'digging-deeper' => 'Advanced framework features and capabilities',
            'security' => 'Authentication, authorization, and security features',
            'database' => 'Database interactions, migrations, and seeding',
            'eloquent' => 'Eloquent ORM for elegant database interactions',
            'testing' => 'Testing tools and methodologies',
            'packages' => 'Creating and integrating packages',
        ];
        
        return $descriptions[$category] ?? 'Documentation category';
    }
    
    /**
     * Show a specific documentation page.
     *
     * @param string $page
     * @param string|null $section
     * @return int
     */
    protected function showPage($page, $section = null)
    {
        // Check if the page includes a category
        if (Str::contains($page, '/')) {
            list($category, $pageName) = explode('/', $page, 2);
            $pagePath = $this->docsPath . '/' . $category . '/' . $pageName . '.md';
        } else {
            // Try to find the page in the root directory
            $pagePath = $this->docsPath . '/' . $page . '.md';
            
            // If not found, try to find it in any category
            if (!File::exists($pagePath)) {
                foreach ($this->getDocumentationCategories() as $categoryPath) {
                    $categoryPagePath = $categoryPath . '/' . $page . '.md';
                    if (File::exists($categoryPagePath)) {
                        $pagePath = $categoryPagePath;
                        break;
                    }
                }
            }
        }
        
        if (!File::exists($pagePath)) {
            $this->components->error("Documentation page '{$page}' not found.");
            return Command::FAILURE;
        }
        
        $content = File::get($pagePath);
        
        // If section is specified, try to find and display only that section
        if ($section) {
            $pattern = '/## ' . preg_quote($section, '/') . '.*?(?=## |$)/s';
            if (preg_match($pattern, $content, $matches)) {
                $content = $matches[0];
            } else {
                $this->components->error("Section '{$section}' not found in page '{$page}'.");
                return Command::FAILURE;
            }
        }
        
        // Display the content
        $this->line("\n<fg=blue;options=bold>" . strtoupper($page) . "</>\n");
        $this->line($this->formatMarkdown($content));
        
        return Command::SUCCESS;
    }
    
    /**
     * Search the documentation for specific content.
     *
     * @param string $query
     * @return int
     */
    protected function searchDocs($query)
    {
        $results = [];
        
        // Search in root directory
        $rootPages = File::glob($this->docsPath . '/*.md');
        foreach ($rootPages as $page) {
            $this->searchInFile($page, $query, $results);
        }
        
        // Search in all categories
        foreach ($this->getDocumentationCategories() as $categoryPath) {
            $category = basename($categoryPath);
            $pages = $this->getPagesInCategory($category);
            
            foreach ($pages as $page) {
                $this->searchInFile($page, $query, $results, $category);
            }
        }
        
        if (empty($results)) {
            $this->components->warn("No results found for query: '{$query}'");
            return Command::FAILURE;
        }
        
        $this->components->info("Search results for: '{$query}'");
        
        foreach ($results as $result) {
            $path = isset($result['category']) 
                ? "<fg=green>{$result['category']}/{$result['page']}</>" 
                : "<fg=green>{$result['page']}</>";
                
            $this->line("{$path} (line {$result['line']}): {$result['content']}");
        }
        
        $this->newLine();
        $this->components->info('To view a page, run: php bsidlify docs [category]/[page]');
        
        return Command::SUCCESS;
    }
    
    /**
     * Search for query in a specific file.
     *
     * @param string $filePath
     * @param string $query
     * @param array &$results
     * @param string|null $category
     * @return void
     */
    protected function searchInFile($filePath, $query, &$results, $category = null)
    {
        $content = File::get($filePath);
        $pageName = basename($filePath, '.md');
        
        if (Str::contains(strtolower($content), strtolower($query))) {
            // Find the lines containing the query
            $lines = explode("\n", $content);
            foreach ($lines as $lineNumber => $line) {
                if (Str::contains(strtolower($line), strtolower($query))) {
                    $result = [
                        'page' => $pageName,
                        'line' => $lineNumber + 1,
                        'content' => $this->truncateAndHighlight($line, $query),
                    ];
                    
                    if ($category) {
                        $result['category'] = $category;
                    }
                    
                    $results[] = $result;
                }
            }
        }
    }
    
    /**
     * Get all available documentation pages.
     *
     * @return array
     */
    protected function getAvailablePages()
    {
        return File::glob($this->docsPath . '/*.md');
    }
    
    /**
     * Generate default documentation files.
     *
     * @return void
     */
    protected function generateDefaultDocs()
    {
        // Create index page
        File::put($this->docsPath . '/index.md', $this->getIndexContent());
        
        // Create installation page
        File::put($this->docsPath . '/installation.md', $this->getInstallationContent());
        
        // Create templating page
        File::put($this->docsPath . '/templating.md', $this->getTemplatingContent());
        
        // Create configuration page
        File::put($this->docsPath . '/configuration.md', $this->getConfigurationContent());
    }
    
    /**
     * Format markdown for console output.
     *
     * @param string $markdown
     * @return string
     */
    protected function formatMarkdown($markdown)
    {
        // Replace escaped newlines with actual newlines
        $markdown = str_replace('\n', PHP_EOL, $markdown);
        
        // Replace headers
        $markdown = preg_replace('/^# (.*?)$/m', '<fg=blue;options=bold>$1</>', $markdown);
        $markdown = preg_replace('/^## (.*?)$/m', '<fg=green;options=bold>$1</>', $markdown);
        $markdown = preg_replace('/^### (.*?)$/m', '<fg=yellow;options=bold>$1</>', $markdown);
        
        // Replace code blocks
        $markdown = preg_replace_callback('/```(.*?)```/s', function ($matches) {
            return '<fg=gray>' . $matches[1] . '</>';
        }, $markdown);
        
        // Replace inline code
        $markdown = preg_replace('/`(.*?)`/', '<fg=gray>$1</>', $markdown);
        
        return $markdown;
    }
    
    /**
     * Truncate and highlight search results.
     *
     * @param string $line
     * @param string $query
     * @return string
     */
    protected function truncateAndHighlight($line, $query)
    {
        $line = trim($line);
        $maxLength = 100;
        
        if (strlen($line) > $maxLength) {
            $position = stripos($line, $query);
            $start = max(0, $position - 40);
            $line = '...' . substr($line, $start, $maxLength) . '...';
        }
        
        return preg_replace('/(' . preg_quote($query, '/') . ')/i', '<fg=yellow;options=bold>$1</>', $line);
    }
    
    /**
     * Get content for index page.
     *
     * @return string
     */
    protected function getIndexContent()
    {
        return "# Bsidlify Documentation

## Introduction

Bsidlify is an elegant, powerful PHP framework designed for web artisans who demand both flexibility and performance. Built on top of modern PHP principles and best practices, Bsidlify empowers developers to create robust, scalable web applications without sacrificing development speed or code quality.

## Key Features

- **Multi-Template Engine Support**: Seamlessly switch between Blade, Twig, and Plates template engines
- **Performance-Optimized Core**: Intelligent caching mechanisms, reduced overhead, and optimized database queries
- **Developer-Friendly CLI**: Enhanced command-line interface with `php bsidlify` commands
- **Modern Architecture**: Built on PHP 8.2+ with a focus on type safety and modern programming patterns
- **Flexible Routing System**: Define elegant routes with middleware, model binding, and advanced pattern matching
- **Smart Configuration**: Environment-based configuration with sensible defaults and easy customization
- **Comprehensive Testing Support**: First-class testing tools and helpers for TDD workflows

## Documentation Pages

- [Installation](installation)
- [Configuration](configuration)
- [Templating](templating)

## Getting Help

If you need assistance with Bsidlify, you can:

- Check the official documentation (you're reading it now!)
- Visit the GitHub repository at https://github.com/bsidlify4u/bsidlify
- Join our community forums or chat channels
";
    }
    
    /**
     * Get content for installation page.
     *
     * @return string
     */
    protected function getInstallationContent()
    {
        return "# Installation

## System Requirements

- PHP >= 8.2
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Installation Methods

### Via Composer Create-Project

```bash
composer create-project bsidlify/bsidlify my-project
cd my-project
php bsidlify key:generate
```

### Clone Repository

```bash
git clone https://github.com/bsidlify/bsidlify.git my-project
cd my-project
composer install
cp .env.example .env
php bsidlify key:generate
```

## Quick Start Guide

### Start Development Server

```bash
php bsidlify serve
```

### Run Development Environment with All Services

```bash
composer run dev
```

This starts concurrent processes:
- PHP development server
- Queue worker
- Log monitor
- Vite development server
";
    }
    
    /**
     * Get content for templating page.
     *
     * @return string
     */
    protected function getTemplatingContent()
    {
        return "# Templating

Bsidlify's unique multi-template engine system allows developers to choose their preferred syntax or use different engines for different parts of their application.

## Configuration

Configure template engines in `config/view.php`:

```php
// Select the default engine
'default_engine' => env('VIEW_ENGINE', 'blade'),

// Configure each engine
'engines' => [
    'blade' => [
        'extension' => 'blade.php',
    ],
    'twig' => [
        'extension' => 'twig',
        'options' => [
            'cache' => storage_path('framework/twig'),
            'debug' => env('APP_DEBUG', false),
            'auto_reload' => env('APP_DEBUG', false),
        ],
    ],
    'plates' => [
        'extension' => 'plate.php',
        'options' => [
            'folder_namespace' => 'views',
            'directory' => resource_path('views'),
        ],
    ],
],
```

## Using Blade Templates

```php
// In controller
return view('example.blade-template', \$data);

// In template
<h1>{{ \$title }}</h1>
@if(condition)
    <p>Conditional content</p>
@endif
```

## Using Twig Templates

```php
// In controller
use App\\Facades\\Template;
return response(Template::driver('twig')->render('example.twig-template', \$data));

// In template
<h1>{{ title }}</h1>
{% if condition %}
    <p>Conditional content</p>
{% endif %}
```

## Using Plates Templates

```php
// In controller
use App\\Facades\\Template;
return response(Template::driver('plates')->render('example.plate-template', \$data));

// In template
<h1><?= \$this->e(\$title) ?></h1>
<?php if (\$condition): ?>
    <p>Conditional content</p>
<?php endif; ?>
```

## Auto-Detection of Template Engines

```php
// Automatically selects engine based on file extension
use App\\Facades\\Template;
return response(Template::render('example.view-name', \$data));
```
";
    }
    
    /**
     * Get content for configuration page.
     *
     * @return string
     */
    protected function getConfigurationContent()
    {
        return "# Configuration

Bsidlify follows the convention-over-configuration principle, with sensible defaults and easy customization through environment variables and configuration files.

## Key Configuration Files

- `.env`: Environment-specific variables and settings
- `config/app.php`: Application configuration
- `config/view.php`: Template engine configuration
- `config/database.php`: Database connections and settings
- `config/cache.php`: Cache store configurations
- `config/queue.php`: Queue driver settings

## Environment Configuration

The `.env` file contains environment-specific configuration values. You should never commit this file to version control. A `.env.example` file is included with the framework as a starting point.

Key environment variables include:

```
APP_NAME=Bsidlify
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bsidlify
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

VIEW_ENGINE=blade
```

## Configuration Caching

To improve performance in production, you can cache your configuration files:

```bash
php bsidlify config:cache
```

To clear the configuration cache:

```bash
php bsidlify config:clear
```
";
    }
}