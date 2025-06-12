<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

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