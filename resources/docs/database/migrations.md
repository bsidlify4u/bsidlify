# Database Migrations

- [Introduction](#introduction)
- [Generating Migrations](#generating-migrations)
- [Migration Structure](#migration-structure)
- [Running Migrations](#running-migrations)
  - [Rolling Back Migrations](#rolling-back-migrations)
- [Tables](#tables)
  - [Creating Tables](#creating-tables)
  - [Updating Tables](#updating-tables)
  - [Renaming / Dropping Tables](#renaming--dropping-tables)
- [Columns](#columns)
  - [Creating Columns](#creating-columns)
  - [Available Column Types](#available-column-types)
  - [Column Modifiers](#column-modifiers)
  - [Modifying Columns](#modifying-columns)
  - [Dropping Columns](#dropping-columns)
- [Indexes](#indexes)
  - [Creating Indexes](#creating-indexes)
  - [Dropping Indexes](#dropping-indexes)
- [Foreign Keys](#foreign-keys)
- [Enhanced Migration Features](#enhanced-migration-features)
  - [Pre-Migration Checks](#pre-migration-checks)
  - [Migration Status](#migration-status)

<a name="introduction"></a>
## Introduction

Migrations are like version control for your database, allowing your team to define and share the application's database schema. If you have ever had to tell a teammate to manually add a column to their local database schema after pulling in your changes from source control, you've faced the problem that database migrations solve.

Bsidlify's migration features extend Laravel's migration capabilities with enhanced pre-migration checks, better error handling, and more detailed status reporting. These improvements help ensure smoother database schema changes and reduce the risk of failed migrations.

<a name="generating-migrations"></a>
## Generating Migrations

You may use the `make:migration` command to generate a database migration:

```bash
php bsidlify make:migration create_users_table
```

The new migration will be placed in your `database/migrations` directory. Each migration file name contains a timestamp, which allows Bsidlify to determine the order of the migrations.

The `--table` and `--create` options may also be used to indicate the name of the table and whether the migration will be creating a new table:

```bash
php bsidlify make:migration add_votes_to_users_table --table=users
php bsidlify make:migration create_users_table --create=users
```

<a name="migration-structure"></a>
## Migration Structure

A migration class contains two methods: `up` and `down`. The `up` method is used to add new tables, columns, or indexes to your database, while the `down` method should reverse the operations performed by the `up` method.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

<a name="running-migrations"></a>
## Running Migrations

To run all of your outstanding migrations, execute the `migrate` command:

```bash
php bsidlify migrate
```

Bsidlify enhances the migration process by performing pre-migration checks that verify database connections and detect potential table conflicts before running migrations, providing a smoother experience.

If you would like to see which migrations are going to be run without actually running them, you can use the `--pretend` flag:

```bash
php bsidlify migrate --pretend
```

<a name="rolling-back-migrations"></a>
### Rolling Back Migrations

To roll back the latest migration operation, you may use the `rollback` command:

```bash
php bsidlify migrate:rollback
```

You may roll back a limited number of migrations by providing the `step` option to the `rollback` command:

```bash
php bsidlify migrate:rollback --step=5
```

To roll back all of your migrations, you may use the `reset` command:

```bash
php bsidlify migrate:reset
```

<a name="tables"></a>
## Tables

<a name="creating-tables"></a>
### Creating Tables

To create a new database table, use the `create` method on the `Schema` facade:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});
```

<a name="updating-tables"></a>
### Updating Tables

The `table` method on the `Schema` facade may be used to update existing tables:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->after('email');
});
```

<a name="renaming--dropping-tables"></a>
### Renaming / Dropping Tables

To rename an existing database table, use the `rename` method:

```php
Schema::rename('old_table_name', 'new_table_name');
```

To drop an existing table, you may use the `drop` or `dropIfExists` methods:

```php
Schema::drop('users');
Schema::dropIfExists('users');
```

<a name="columns"></a>
## Columns

<a name="creating-columns"></a>
### Creating Columns

The `Schema` facade provides a variety of column types that you may use when building your tables:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name', 100);
});
```

<a name="available-column-types"></a>
### Available Column Types

Bsidlify supports all column types available in Laravel, including:

```php
$table->id();
$table->bigInteger('votes');
$table->binary('data');
$table->boolean('confirmed');
$table->char('name', 100);
$table->date('created_at');
$table->dateTime('created_at');
$table->decimal('amount', 8, 2);
$table->double('amount', 8, 2);
$table->enum('level', ['easy', 'hard']);
$table->float('amount', 8, 2);
$table->foreignId('user_id');
$table->integer('votes');
$table->json('options');
$table->jsonb('options');
$table->longText('description');
$table->mediumInteger('votes');
$table->mediumText('description');
$table->morphs('taggable');
$table->nullableMorphs('taggable');
$table->smallInteger('votes');
$table->string('name', 100);
$table->text('description');
$table->time('sunrise');
$table->timestamp('added_at');
$table->timestamps();
$table->tinyInteger('votes');
$table->tinyText('notes');
$table->unsignedBigInteger('votes');
$table->unsignedInteger('votes');
$table->unsignedMediumInteger('votes');
$table->unsignedSmallInteger('votes');
$table->unsignedTinyInteger('votes');
$table->uuid('id');
$table->year('birth_year');
```

<a name="column-modifiers"></a>
### Column Modifiers

In addition to the column types listed above, there are several column "modifiers" you may use:

```php
$table->string('email')->after('name');
$table->string('email')->autoIncrement();
$table->string('email')->comment('Email address');
$table->string('email')->default('example@example.com');
$table->string('email')->first();
$table->string('email')->nullable();
$table->string('email')->storedAs('first_name || last_name');
$table->string('email')->unique();
$table->string('email')->unsigned();
$table->string('email')->useCurrent();
$table->string('email')->useCurrentOnUpdate();
$table->string('email')->virtualAs('first_name || last_name');
$table->timestamp('added_at')->useCurrent();
$table->timestamp('updated_at')->useCurrentOnUpdate();
```

<a name="modifying-columns"></a>
### Modifying Columns

Bsidlify enhances Laravel's column modification capabilities with improved error handling and reporting. To modify a column, you can use the `change` method:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name', 50)->change();
});
```

<a name="dropping-columns"></a>
### Dropping Columns

To drop a column, you may use the `dropColumn` method:

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('votes');
});
```

You may drop multiple columns from a table by passing an array of column names to the `dropColumn` method:

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn(['votes', 'avatar', 'location']);
});
```

<a name="indexes"></a>
## Indexes

<a name="creating-indexes"></a>
### Creating Indexes

Bsidlify supports several types of indexes:

```php
// Unique index
$table->string('email')->unique();

// Basic index
$table->string('email')->index();

// Primary key
$table->primary('id');

// Composite keys
$table->primary(['id', 'parent_id']);

// Named index
$table->unique('email', 'unique_email');
```

<a name="dropping-indexes"></a>
### Dropping Indexes

To drop an index, you must specify the index's name:

```php
$table->dropIndex('users_email_index');
```

<a name="foreign-keys"></a>
## Foreign Keys

Bsidlify also provides support for creating foreign key constraints:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->foreignId('user_id')
          ->constrained()
          ->onUpdate('cascade')
          ->onDelete('cascade');
});
```

Alternative syntax for defining foreign key constraints:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');
});
```

To drop a foreign key, you may use the `dropForeign` method:

```php
$table->dropForeign('posts_user_id_foreign');
```

<a name="enhanced-migration-features"></a>
## Enhanced Migration Features

Bsidlify extends Laravel's migration capabilities with several enhanced features.

<a name="pre-migration-checks"></a>
### Pre-Migration Checks

Before running migrations, Bsidlify performs several checks to ensure a smoother migration process:

1. **Database Connection Verification**: Ensures the database connection is working correctly before attempting migrations.

2. **Table Conflict Detection**: Checks for potential conflicts with existing tables before creating new ones.

3. **Migration Dependency Analysis**: Analyzes migration dependencies to ensure they run in the correct order.

To run only the pre-migration checks without executing the migrations, use:

```bash
php bsidlify migrate:check
```

<a name="migration-status"></a>
### Migration Status

Bsidlify provides an enhanced migration status command that shows more detailed information about your migrations:

```bash
php bsidlify migrate:status
```

This command shows:

- Which migrations have been run
- When they were run
- How long each migration took to execute
- Any pending migrations that haven't been run yet

You can also get a detailed report of a specific migration:

```bash
php bsidlify migrate:status --migration=2023_01_01_000000_create_users_table
```

This enhanced status reporting helps you understand your database migration history better and identify any potential issues.
