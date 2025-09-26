<?php

return [
    'default' => env('FILESYSTEM_DISK', 'public'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'media_inbox' => [
            'driver' => 'local',
            'root' => env('MEDIA_INBOX', storage_path('app/media/inbox')),
            'throw' => false,
        ],
        'media_hls' => [
            'driver' => 'local',
            'root' => env('MEDIA_HLS', storage_path('app/media/hls')),
            'visibility' => 'public',
        ],
        'media_archive' => [
            'driver' => 'local',
            'root' => env('MEDIA_ARCHIVE', storage_path('app/media/archive')),
        ],
        'secure_documents' => [
            'driver' => 'local',
            'root' => storage_path('app/secure-documents'),
            'visibility' => 'private',
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('media/hls') => env('MEDIA_HLS', storage_path('app/media/hls')),
    ],

    'verification_disk' => env('VERIFICATION_DISK', 'secure_documents'),
];
