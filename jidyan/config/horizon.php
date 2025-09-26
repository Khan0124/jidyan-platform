<?php

return [
    'domain' => env('HORIZON_DOMAIN'),
    'path' => 'horizon',
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', 'horizon:'),
    'middleware' => ['web', 'auth'],
    'waits' => [
        'redis:default' => 60,
    ],
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'maxProcesses' => 10,
                'minProcesses' => 1,
                'tries' => 3,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'maxProcesses' => 3,
                'tries' => 1,
            ],
        ],
    ],
];
