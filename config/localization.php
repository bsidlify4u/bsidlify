<?php

return [
    'default' => env('LOCALE_DRIVER', 'file'),

    'drivers' => [
        'file' => [
            'path' => resource_path('lang'),
        ],
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'table' => 'translations',
        ],
    ],

    'available_locales' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'regional' => 'en_US',
        ],
        'es' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'regional' => 'es_ES',
        ],
        // Add more locales as needed
    ],

    'plural_rules' => [
        'en' => [
            'zero' => 0,
            'one' => 1,
            'other' => [2, 999999],
        ],
        'ar' => [
            'zero' => 0,
            'one' => 1,
            'two' => 2,
            'few' => [3, 10],
            'many' => [11, 99],
            'other' => [100, 999999],
        ],
    ],

    'cache' => [
        'enabled' => env('LOCALE_CACHE_ENABLED', true),
        'ttl' => env('LOCALE_CACHE_TTL', 60 * 24), // 24 hours
    ],

    'fallback_locale' => env('FALLBACK_LOCALE', 'en'),

    'load_missing_keys' => env('APP_DEBUG', false),
];
