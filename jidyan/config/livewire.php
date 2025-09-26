<?php

return [
    'class_namespace' => 'App\\Http\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',
    'lazy_placeholder' => null,
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TMP_DISK', 'local'),
        'rules' => ['file', 'max:122880'],
        'directory' => null,
        'middleware' => null,
    ],
];
