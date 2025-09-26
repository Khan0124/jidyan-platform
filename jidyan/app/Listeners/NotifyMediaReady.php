<?php

namespace App\Listeners;

use App\Events\MediaProcessingCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\MediaReadyNotification;

class NotifyMediaReady implements ShouldQueue
{
    public function handle(MediaProcessingCompleted $event): void
    {
        $player = $event->media->player->user;
        $player->notify(new MediaReadyNotification($event->media));
    }
}
