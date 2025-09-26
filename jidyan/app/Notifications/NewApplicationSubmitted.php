<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Application $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('New application from :player', ['player' => $this->application->player->user->name]))
            ->line(__('A new application has been received for :opportunity', ['opportunity' => $this->application->opportunity->title]))
            ->action(__('Review Applications'), route('dashboard.club.applications.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'opportunity_id' => $this->application->opportunity_id,
            'player_id' => $this->application->player_id,
        ];
    }
}
