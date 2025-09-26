<?php

namespace App\Notifications;

use App\Models\PlayerMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MediaReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public PlayerMedia $media)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('Your video is ready'))
            ->line(__('Your media :media is now available for viewing.', ['media' => $this->media->id]))
            ->action(__('View Media'), route('dashboard.player.profile.edit'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'media_id' => $this->media->id,
            'player_id' => $this->media->player_id,
            'status' => $this->media->status,
        ];
    }
}
