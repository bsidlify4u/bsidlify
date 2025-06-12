# Installation


- [Meet Bsidlify](#meet-bsidlify)
  - [Why Bsidlify?](#why-bsidlify)
- [System Requirements](#system-requirements)
- [Installing Bsidlify](#installing-bsidlify)
  - [Via Composer Create-Project](#via-composer-create-project)
  - [Via Git Clone](#via-git-clone)
- [Initial Configuration](#initial-configuration)
  - [Environment Configuration](#environment-configuration)
  - [Database Configuration](#database-configuration)
- [Development Server](#development-server)
- [Next Steps](#next-steps)


<a name="meet-bsidlify"></a>
## Meet Bsidlify

Bsidlify is a web application framework with expressive, elegant syntax. A web framework provides a structure and starting point for creating your application, allowing you to focus on creating something amazing while we sweat the details.

Bsidlify strives to provide an amazing developer experience while providing powerful features such as thorough dependency injection, an expressive database abstraction layer, queues and scheduled jobs, unit and integration testing, and more.

Whether you are new to PHP web frameworks or have years of experience, Bsidlify is a framework that can grow with you. We'll help you take your first steps as a web developer or give you a boost as you take your expertise to the next level. We can't wait to see what you build.


<a name="why-bsidlify"></a>
### Why Bsidlify?

There are a variety of tools and frameworks available to you when building a web application. However, we believe Bsidlify is an excellent choice for building modern, full-stack web applications.

#### A Flexible Framework

Bsidlify is designed to be flexible and adaptable to your needs. One of its standout features is the multi-template engine system, which allows you to choose between Blade, Twig, and Plates templating engines based on your preferences or project requirements.

#### Enhanced Error Handling

Bsidlify includes improved error handling throughout the framework, with detailed error messages and helpful suggestions when things go wrong. This makes debugging and development much more efficient.

#### Built on Solid Foundations

Based on Laravel, Bsidlify inherits a robust ecosystem while adding unique features and improvements. You get the best of both worlds: a proven foundation with innovative enhancements.


<a name="system-requirements"></a>
## System Requirements

Before you install Bsidlify, make sure your server meets the following requirements:

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
- Composer


<a name="installing-bsidlify"></a>
## Installing Bsidlify

<a name="via-composer-create-project"></a>
### Via Composer Create-Project

The easiest way to create a new Bsidlify project is via Composer's `create-project` command:

```bash
composer create-project bsidlify/bsidlify my-project
cd my-project
php bsidlify key:generate
```

This will create a new Bsidlify project in the `my-project` directory, install all dependencies, and generate an application key.

<a name="via-git-clone"></a>
### Via Git Clone

Alternatively, you can clone the Bsidlify repository from GitHub:

```bash
git clone https://github.com/bsidlify4u/bsidlify.git my-project
cd my-project
composer install
cp .env.example .env
php bsidlify key:generate
```


<a name="initial-configuration"></a>
## Initial Configuration

<a name="environment-configuration"></a>
### Environment Configuration

After installation, you should configure your environment variables. The `.env` file contains configuration values specific to your environment, such as database credentials, mail settings, and more.

All of the configuration files for the Bsidlify framework are stored in the `config` directory. Each option is documented, so feel free to look through the files and get familiar with the options available to you.

Bsidlify needs almost no additional configuration out of the box. You are free to get started developing! However, you may wish to review the `config/app.php` file and its documentation. It contains several options such as `timezone` and `locale` that you may wish to change according to your application.


<a name="database-configuration"></a>
### Database Configuration

By default, Bsidlify is configured to use SQLite for database storage. If you prefer to use another database like MySQL, PostgreSQL, or SQL Server, you can update your `.env` file with the appropriate database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bsidlify
DB_USERNAME=root
DB_PASSWORD=
```

After configuring your database, you should run migrations to create the necessary database tables:

```bash
php bsidlify migrate
```

Bsidlify includes an enhanced migration system with pre-migration checks that verify database connections and detect potential table conflicts before running migrations, providing a smoother experience.


<a name="development-server"></a>
## Development Server

If you have PHP installed locally and you would like to use PHP's built-in development server to serve your application, you may use the `serve` command:

```bash
php bsidlify serve
```

This command will start a development server at `http://localhost:8000`.

For a more complete development environment, you can use the `dev` command provided in the `composer.json` file:

```bash
composer run dev
```

This will start concurrent processes including:
- PHP development server
- Queue worker
- Log monitor
- Vite development server (for frontend assets)


<a name="next-steps"></a>
## Next Steps

Now that you have created your Bsidlify application, you may be wondering what to learn next. First, we strongly recommend becoming familiar with how Bsidlify works by reading the following documentation:

- [Request Lifecycle](../architecture/lifecycle.md)
- [Configuration](configuration.md)
- [Directory Structure](directory-structure.md)
- [Template Engines](../basics/templates.md)
- [Service Container](../architecture/container.md)
- [Facades](../architecture/facades.md)

Bsidlify is designed to be both powerful and easy to use. We hope you enjoy building your next application with Bsidlify!
