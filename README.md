# Bsidlify Framework

<p align="center">
    <img src="https://pbs.twimg.com/profile_images/1919384588679331840/shMwCk-E_400x400.jpg" alt="Bsidlify Logo" width="100" height="100">
</p>

## About Bsidlify

Bsidlify is an elegant, powerful PHP framework designed for web artisans who demand both flexibility and performance. Built on top of modern PHP principles and best practices, Bsidlify empowers developers to create robust, scalable web applications without sacrificing development speed or code quality.

### Key Features

- **Multi-Template Engine Support**: Seamlessly switch between Blade, Twig, and Plates template engines
- **Performance-Optimized Core**: Intelligent caching mechanisms, reduced overhead, and optimized database queries
- **Developer-Friendly CLI**: Enhanced command-line interface with `php bsidlify` commands
- **Modern Architecture**: Built on PHP 8.2+ with a focus on type safety and modern programming patterns
- **Flexible Routing System**: Define elegant routes with middleware, model binding, and advanced pattern matching
- **Smart Configuration**: Environment-based configuration with sensible defaults and easy customization
- **Comprehensive Testing Support**: First-class testing tools and helpers for TDD workflows

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

## Installation

### Via Composer Create-Project

```bash
composer create-project bsidlify/bsidlify my-project
cd my-project
php bsidlify key:generate
```

### Clone Repository

```bash
git clone https://github.com/bsidlify4u/bsidlify.git my-project
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

## Configuration

Bsidlify follows the convention-over-configuration principle, with sensible defaults and easy customization through environment variables and configuration files.

### Key Configuration Files

- `.env`: Environment-specific variables and settings
- `config/app.php`: Application configuration
- `config/view.php`: Template engine configuration
- `config/database.php`: Database connections and settings
- `config/cache.php`: Cache store configurations
- `config/queue.php`: Queue driver settings

## Template Engine System

Bsidlify's unique multi-template engine system allows developers to choose their preferred syntax or use different engines for different parts of their application.

### Configuration

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

### Using Blade Templates

```php
// In controller
return view('example.blade-template', $data);

// In template
<h1>{{ $title }}</h1>
@if(condition)
    <p>Conditional content</p>
@endif
```

### Using Twig Templates

```php
// In controller
use App\Facades\Template;
return response(Template::driver('twig')->render('example.twig-template', $data));

// In template
<h1>{{ title }}</h1>
{% if condition %}
    <p>Conditional content</p>
{% endif %}
```

### Using Plates Templates

```php
// In controller
use App\Facades\Template;
return response(Template::driver('plates')->render('example.plate-template', $data));

// In template
<h1><?= $this->e($title) ?></h1>
<?php if ($condition): ?>
    <p>Conditional content</p>
<?php endif; ?>
```

### Auto-Detection of Template Engines

```php
// Automatically selects engine based on file extension
use App\Facades\Template;
return response(Template::render('example.view-name', $data));
```

## CLI Commands

Bsidlify extends Laravel's Artisan with enhanced commands through the `php bsidlify` command-line tool.

```bash
# List all available commands
php bsidlify list

# Create components
php bsidlify make:controller UserController
php bsidlify make:model User
php bsidlify make:migration create_users_table

# Database operations
php bsidlify migrate
php bsidlify db:seed
php bsidlify db:wipe

# Application maintenance
php bsidlify cache:clear
php bsidlify config:cache
php bsidlify route:cache
php bsidlify view:clear

# Development server
php bsidlify serve
```

## Directory Structure

```
app/                  # Application code
├── Console/          # Console commands and kernel
├── Exceptions/       # Exception handlers
├── Facades/          # Facade implementations
├── Http/             # Controllers, middleware, and requests
├── Models/           # Eloquent models
├── Providers/        # Service providers
├── Template/         # Template engine implementations
bootstrap/            # Framework bootstrap files
config/               # Configuration files
database/             # Migrations and seeders
├── factories/        # Model factories
├── migrations/       # Database migrations
├── seeders/          # Database seeders
public/               # Publicly accessible files
resources/            # Views, assets, language files
├── css/              # CSS source files
├── js/               # JavaScript source files
├── lang/             # Language files
├── views/            # Templates (Blade, Twig, Plates)
routes/               # Route definitions
├── api.php           # API routes
├── web.php           # Web routes
├── console.php       # Console commands
storage/              # Application storage
├── app/              # Application-generated files
├── framework/        # Framework-generated files
├── logs/             # Log files
tests/                # Test cases
```

## Database Management

Bsidlify provides robust tools for database management, migrations, and seeding.

### Migrations

```bash
# Create a migration
php bsidlify make:migration create_posts_table

# Run migrations
php bsidlify migrate

# Rollback migrations
php bsidlify migrate:rollback

# Reset and re-run all migrations
php bsidlify migrate:fresh
```

### Models and Eloquent ORM

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title', 'content', 'user_id',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

## Testing

Bsidlify includes comprehensive testing tools powered by PHPUnit.

```bash
# Run all tests
php bsidlify test

# Run specific test file
php bsidlify test --filter=UserTest

# Generate test coverage report
php bsidlify test --coverage
```

## Security

Bsidlify takes security seriously, with built-in protection against common web vulnerabilities:

- CSRF protection
- XSS prevention
- SQL injection prevention
- Authentication and authorization systems
- Encryption and hashing utilities
- Secure password handling

## Performance Optimization

Bsidlify includes several performance optimization features:

- Route caching
- Configuration caching
- View caching
- Query caching
- Optimized autoloader
- Just-in-time compilation for Blade templates

## Contributing

We welcome contributions to the Bsidlify framework! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Code of Conduct

In order to ensure that the Bsidlify community is welcoming to all, please review and abide by the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

The Bsidlify framework is open-source software licensed under the [MIT license](https://github.com/bsidlify4u/bsidlify?tab=MIT-1-ov-file).
