<?php

namespace App\Providers;

use App\Events\MediaProcessingCompleted;
use App\Listeners\NotifyMediaReady;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MediaProcessingCompleted::class => [
            NotifyMediaReady::class,
        ],
    ];
}
