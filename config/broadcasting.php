<?php

return [
    'broadcast_driver' => env('BROADCAST_DRIVER', 'redis'),
    
    'broadcast_connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
            'key_prefix' => env('BROADCAST_REDIS_PREFIX', 'bsidlify_broadcast:'),
        ],
        'socket.io' => [
            'driver' => 'socket.io',
            'host' => env('SOCKET_IO_HOST', 'localhost'),
            'port' => env('SOCKET_IO_PORT', 6001),
            'options' => [
                'cluster' => env('SOCKET_IO_CLUSTER', 'mqtt'),
                'useTLS' => env('SOCKET_IO_TLS', false),
            ],
        ],
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],
    ],
];
