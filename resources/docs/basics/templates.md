# Template Engines

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Blade Templates](#blade-templates)
- [Twig Templates](#twig-templates)
- [Plates Templates](#plates-templates)
- [Auto-Detection](#auto-detection)
- [Sharing Data](#sharing-data)
- [Template Inheritance](#template-inheritance)
- [Custom Template Engines](#custom-template-engines)

<a name="introduction"></a>
## Introduction

Bsidlify provides a unique multi-template engine system that allows developers to choose their preferred syntax or use different engines for different parts of their application. This flexibility makes Bsidlify stand out from other frameworks, giving you the freedom to use the templating syntax that best suits your needs.

The framework includes built-in support for three popular template engines:

- **Blade**: Laravel's powerful templating engine with simple, yet expressive syntax
- **Twig**: A flexible, fast, and secure template engine for PHP
- **Plates**: A native PHP template system that's focused on simplicity

<a name="configuration"></a>
## Configuration

Template engine configuration is stored in the `config/view.php` file. Here you can specify the default engine and configure settings for each supported engine:

```php
// config/view.php

return [
    // Other view configuration...

    /*
    |--------------------------------------------------------------------------
    | Default Templating Engine
    |--------------------------------------------------------------------------
    |
    | This option controls which templating engine should be used by default
    | when rendering views. Supported engines: blade, twig, plates
    |
    */

    'default_engine' => env('VIEW_ENGINE', 'blade'),

    /*
    |--------------------------------------------------------------------------
    | Templating Engines Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each templating engine.
    |
    */

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
                'strict_variables' => env('APP_DEBUG', false),
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
];
```

You can change the default template engine by updating the `VIEW_ENGINE` variable in your `.env` file:

```
VIEW_ENGINE=twig
```

<a name="blade-templates"></a>
## Blade Templates

[Blade](https://laravel.com/docs/blade) is a simple, yet powerful templating engine provided with Laravel. Blade template files use the `.blade.php` extension and are stored in the `resources/views` directory.

### Basic Usage

```php
// In controller
return view('example.blade-template', $data);

// In template (resources/views/example/blade-template.blade.php)
<h1>{{ $title }}</h1>
@if(condition)
    <p>Conditional content</p>
@endif
```

### Blade Directives

Blade provides many convenient directives for common tasks:

```blade
{{-- Comments --}}

{{-- Displaying Data --}}
{{ $variable }}
{!! $unescaped_html !!}

{{-- Control Structures --}}
@if ($condition)
    // Content
@elseif ($otherCondition)
    // Content
@else
    // Content
@endif

@foreach ($items as $item)
    {{ $item }}
@endforeach

@while ($condition)
    // Content
@endwhile

{{-- Including Partials --}}
@include('partials.header')

{{-- Template Inheritance --}}
@extends('layouts.app')

@section('content')
    // Content
@endsection
```

<a name="twig-templates"></a>
## Twig Templates

[Twig](https://twig.symfony.com/) is a flexible, fast, and secure template engine for PHP. Twig template files use the `.twig` extension and are stored in the `resources/views` directory.

### Basic Usage

```php
// In controller
use App\Facades\Template;
return response(Template::driver('twig')->render('example.twig-template', $data));

// In template (resources/views/example/twig-template.twig)
<h1>{{ title }}</h1>
{% if condition %}
    <p>Conditional content</p>
{% endif %}
```

### Twig Syntax

Twig provides its own syntax for common tasks:

```twig
{# Comments #}

{# Displaying Data #}
{{ variable }}
{{ variable|raw }} {# Unescaped HTML #}

{# Control Structures #}
{% if condition %}
    {# Content #}
{% elseif otherCondition %}
    {# Content #}
{% else %}
    {# Content #}
{% endif %}

{% for item in items %}
    {{ item }}
{% endfor %}

{# Including Partials #}
{% include 'partials/header.twig' %}

{# Template Inheritance #}
{% extends 'layouts/app.twig' %}

{% block content %}
    {# Content #}
{% endblock %}
```

<a name="plates-templates"></a>
## Plates Templates

[Plates](https://platesphp.com/) is a native PHP template system that's focused on simplicity. Plates template files use the `.plate.php` extension and are stored in the `resources/views` directory.

### Basic Usage

```php
// In controller
use App\Facades\Template;
return response(Template::driver('plates')->render('example.plate-template', $data));

// In template (resources/views/example/plate-template.plate.php)
<h1><?= $this->e($title) ?></h1>
<?php if ($condition): ?>
    <p>Conditional content</p>
<?php endif; ?>
```

### Plates Syntax

Plates uses native PHP syntax with helper methods:

```php
<?php // Comments ?>

<?php // Displaying Data ?>
<?= $this->e($variable) ?> <!-- Escaped output -->
<?= $variable ?> <!-- Unescaped output -->

<?php // Control Structures ?>
<?php if ($condition): ?>
    <!-- Content -->
<?php elseif ($otherCondition): ?>
    <!-- Content -->
<?php else: ?>
    <!-- Content -->
<?php endif; ?>

<?php foreach ($items as $item): ?>
    <?= $this->e($item) ?>
<?php endforeach; ?>

<?php // Including Partials ?>
<?= $this->insert('partials/header') ?>

<?php // Template Inheritance ?>
<?php $this->layout('layouts/app') ?>

<?php $this->start('content') ?>
    <!-- Content -->
<?php $this->stop() ?>
```

<a name="auto-detection"></a>
## Auto-Detection

Bsidlify can automatically detect which template engine to use based on the file extension of the view. This allows you to seamlessly mix different template engines in your application:

```php
// In controller
use App\Facades\Template;
return response(Template::render('example.view-name', $data));
```

The `Template::render()` method will:

1. Look for a view file with the given name
2. Determine the appropriate engine based on the file extension
3. Render the view using that engine

This allows you to organize your views however you prefer, and Bsidlify will handle the rest.

<a name="sharing-data"></a>
## Sharing Data

You can share data across all templates, regardless of the engine used:

```php
// In a service provider or middleware
use App\Facades\Template;

// Share with a specific engine
Template::driver('blade')->share('key', 'value');
Template::driver('twig')->share('key', 'value');
Template::driver('plates')->share('key', 'value');

// Share with all engines
Template::driver('blade')->share($data);
Template::driver('twig')->share($data);
Template::driver('plates')->share($data);
```

<a name="template-inheritance"></a>
## Template Inheritance

Each template engine has its own approach to template inheritance:

### Blade Inheritance

```blade
<!-- layouts/app.blade.php -->
<html>
    <head>
        <title>@yield('title')</title>
    </head>
    <body>
        @yield('content')
    </body>
</html>

<!-- page.blade.php -->
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <p>This is the page content.</p>
@endsection
```

### Twig Inheritance

```twig
{# layouts/app.twig #}
<html>
    <head>
        <title>{% block title %}{% endblock %}</title>
    </head>
    <body>
        {% block content %}{% endblock %}
    </body>
</html>

{# page.twig #}
{% extends 'layouts/app.twig' %}

{% block title %}Page Title{% endblock %}

{% block content %}
    <p>This is the page content.</p>
{% endblock %}
```

### Plates Inheritance

```php
<!-- layouts/app.plate.php -->
<html>
    <head>
        <title><?= $this->section('title') ?></title>
    </head>
    <body>
        <?= $this->section('content') ?>
    </body>
</html>

<!-- page.plate.php -->
<?php $this->layout('layouts/app') ?>

<?php $this->start('title') ?>Page Title<?php $this->stop() ?>

<?php $this->start('content') ?>
    <p>This is the page content.</p>
<?php $this->stop() ?>
```

<a name="custom-template-engines"></a>
## Custom Template Engines

You can add support for additional template engines by creating a class that implements the `App\Template\TemplateEngineInterface` interface:

```php
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
```

Then register your custom engine in a service provider:

```php
use App\Template\CustomEngine;

// In a service provider
$this->app->singleton('template.engines.custom', function ($app) {
    return new CustomEngine($app['view'], 'custom.ext');
});

$this->app->extend('template', function ($manager, $app) {
    $manager->extend('custom', function () use ($app) {
        return $app['template.engines.custom'];
    });
    
    return $manager;
});
```

Now you can use your custom engine:

```php
use App\Facades\Template;
return response(Template::driver('custom')->render('example.view-name', $data));
``` 

## Introduction

Bsidlify provides a unique multi-template engine system that allows developers to choose their preferred syntax or use different engines for different parts of their application.

The framework comes with built-in support for three popular PHP templating engines:

- **Blade**: Laravel's powerful templating engine with features like template inheritance, components, and slots
- **Twig**: A flexible, fast, and secure template engine for PHP
- **Plates**: A native PHP template system that's simple, clean, and doesn't require template compilation

This flexibility allows you to use the templating syntax you're most comfortable with or even mix different engines within the same application.

## Configuration

The template engine configuration is stored in `config/view.php`. By default, Bsidlify uses Blade as the primary template engine, but you can easily change this to Twig or Plates:

```php
'default' => env('TEMPLATE_ENGINE', 'blade'),

'engines' => [
    'blade' => [
        'engine' => \Bsidlify\View\Engines\BladeEngine::class,
        'extension' => 'blade.php',
    ],
    'twig' => [
        'engine' => \Bsidlify\View\Engines\TwigEngine::class,
        'extension' => 'twig',
        'options' => [
            'cache' => storage_path('framework/views/twig'),
            'debug' => env('APP_DEBUG', false),
            'auto_reload' => env('APP_DEBUG', false),
        ],
    ],
    'plates' => [
        'engine' => \Bsidlify\View\Engines\PlatesEngine::class,
        'extension' => 'plates.php',
    ],
],
```

## Basic Usage

### Using the Default Engine

To render a view using the default template engine (as configured in `config/view.php`):

```php
return view('welcome', ['name' => 'John']);
```

### Specifying an Engine

To explicitly specify which template engine to use:

```php
return view('welcome', ['name' => 'John'], 'blade');
// or
return view('welcome', ['name' => 'John'], 'twig');
// or
return view('welcome', ['name' => 'John'], 'plates');
```

### Using the Template Facade

You can also use the `Template` facade to render views:

```php
use Bsidlify\Support\Facades\Template;

return Template::render('welcome', ['name' => 'John']);
// or
return Template::driver('twig')->render('welcome', ['name' => 'John']);
```

## File Structure

Each template engine looks for templates in the `resources/views` directory, but uses different file extensions:

- Blade templates: `*.blade.php`
- Twig templates: `*.twig`
- Plates templates: `*.plates.php`

For example, if you have a view named `welcome`, you would create:

- `resources/views/welcome.blade.php` for Blade
- `resources/views/welcome.twig` for Twig
- `resources/views/welcome.plates.php` for Plates

## Blade Templates

Blade is Laravel's powerful templating engine and is included as the default engine in Bsidlify.

### Basic Syntax

```php
<html>
    <body>
        <h1>Hello, {{ $name }}</h1>
    </body>
</html>
```

### Control Structures

```php
@if ($user->isAdmin())
    <p>This user is an administrator</p>
@endif

@foreach ($users as $user)
    <p>{{ $user->name }}</p>
@endforeach
```

### Template Inheritance

```php
<!-- layouts/app.blade.php -->
<html>
    <head>
        <title>@yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            This is the master sidebar.
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>

<!-- child.blade.php -->
@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    @parent
    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection
```

For more information on Blade, refer to the [Laravel documentation](https://laravel.com/docs/blade).

## Twig Templates

Twig is a modern template engine for PHP with a clean syntax and powerful features.

### Basic Syntax

```twig
<html>
    <body>
        <h1>Hello, {{ name }}</h1>
    </body>
</html>
```

### Control Structures

```twig
{% if user.isAdmin %}
    <p>This user is an administrator</p>
{% endif %}

{% for user in users %}
    <p>{{ user.name }}</p>
{% endfor %}
```

### Template Inheritance

```twig
{# layouts/app.twig #}
<html>
    <head>
        <title>{% block title %}{% endblock %}</title>
    </head>
    <body>
        {% block sidebar %}
            This is the master sidebar.
        {% endblock %}

        <div class="container">
            {% block content %}{% endblock %}
        </div>
    </body>
</html>

{# child.twig #}
{% extends "layouts/app.twig" %}

{% block title %}Page Title{% endblock %}

{% block sidebar %}
    {{ parent() }}
    <p>This is appended to the master sidebar.</p>
{% endblock %}

{% block content %}
    <p>This is my body content.</p>
{% endblock %}
```

For more information on Twig, refer to the [Twig documentation](https://twig.symfony.com/).

## Plates Templates

Plates is a native PHP template system that's lightweight and easy to use.

### Basic Syntax

```php
<html>
    <body>
        <h1>Hello, <?= $this->e($name) ?></h1>
    </body>
</html>
```

### Control Structures

```php
<?php if ($user->isAdmin()): ?>
    <p>This user is an administrator</p>
<?php endif ?>

<?php foreach ($users as $user): ?>
    <p><?= $this->e($user->name) ?></p>
<?php endforeach ?>
```

### Template Inheritance

```php
<!-- layouts/app.plates.php -->
<html>
    <head>
        <title><?= $this->section('title') ?></title>
    </head>
    <body>
        <?php $this->start('sidebar') ?>
            This is the master sidebar.
        <?php $this->stop() ?>
        
        <?= $this->section('sidebar') ?>

        <div class="container">
            <?= $this->section('content') ?>
        </div>
    </body>
</html>

<!-- child.plates.php -->
<?php $this->layout('layouts/app') ?>

<?php $this->start('title') ?>Page Title<?php $this->stop() ?>

<?php $this->start('sidebar') ?>
    <?= $this->parent() ?>
    <p>This is appended to the master sidebar.</p>
<?php $this->stop() ?>

<?php $this->start('content') ?>
    <p>This is my body content.</p>
<?php $this->stop() ?>
```

For more information on Plates, refer to the [Plates documentation](https://platesphp.com/).

## Custom Template Engines

Bsidlify allows you to register custom template engines by implementing the `Bsidlify\Contracts\View\Engine` interface and registering it in the `config/view.php` file:

```php
'engines' => [
    // Other engines...
    'custom' => [
        'engine' => \App\View\Engines\CustomEngine::class,
        'extension' => 'custom.php',
    ],
],
```

Your custom engine class should implement the required methods:

```php
namespace App\View\Engines;

use Bsidlify\Contracts\View\Engine;

class CustomEngine implements Engine
{
    public function render($path, array $data = [])
    {
        // Implementation logic here
    }
}
```

## Switching Engines at Runtime

One of the powerful features of Bsidlify is the ability to switch between template engines at runtime:

```php
// Controller method that returns different template engines based on user preference
public function show(Request $request, $id)
{
    $user = User::findOrFail($id);
    $data = ['user' => $user];
    
    // Get user preference from profile or request
    $engine = $request->user()->preferred_template_engine ?? 'blade';
    
    return view('user.profile', $data, $engine);
}
```

This allows for incredible flexibility in how your application renders views.

## Best Practices

Here are some best practices when working with multiple template engines:

1. **Consistency**: Try to use the same template engine throughout a single feature or module to avoid confusion.

2. **Documentation**: Clearly document which template engine is used for which parts of your application.

3. **Shared Variables**: When passing variables to templates, be aware of the syntax differences between engines.

4. **Performance**: Consider the performance implications of using different template engines, especially in high-traffic applications.

5. **Caching**: Enable template caching in production for all engines to improve performance.

## Conclusion

Bsidlify's multi-template engine system provides flexibility and choice for developers. Whether you prefer the power of Blade, the elegance of Twig, or the simplicity of Plates, Bsidlify makes it easy to use your preferred templating syntax while maintaining a consistent application structure. 
