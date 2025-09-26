<?php

namespace App\Notifications;

use App\Models\Message;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['sender', 'thread']);
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        if ($notifiable->routeNotificationFor('sms') && $this->shouldSendSms()) {
            $channels[] = SmsChannel::class;
        }

        return array_values(array_unique($channels));
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = __('New message from :sender', ['sender' => $this->message->sender->name]);

        return (new MailMessage())
            ->locale($notifiable->preferred_locale ?? app()->getLocale())
            ->subject($subject)
            ->line(__('You have a new message from :sender', ['sender' => $this->message->sender->name]))
            ->line($this->excerpt())
            ->action(__('View conversation'), route('dashboard.messages.index'))
            ->line(__('Open messages dashboard'));
    }

    public function toArray($notifiable): array
    {
        return [
            'thread_id' => $this->message->thread_id,
            'message_id' => $this->message->getKey(),
            'sender_id' => $this->message->sender_user_id,
            'sender_name' => $this->message->sender->name,
            'excerpt' => $this->excerpt(),
            'url' => route('dashboard.messages.index'),
        ];
    }

    public function toSms($notifiable): ?string
    {
        return __('New message from :sender: ":excerpt"', [
            'sender' => $this->message->sender->name,
            'excerpt' => $this->excerpt(),
        ]);
    }

    protected function shouldSendSms(): bool
    {
        $driver = config('services.sms.driver', 'log');

        return ! in_array($driver, [null, '', 'null', 'none'], true);
    }

    protected function excerpt(): string
    {
        return Str::limit((string) $this->message->body, 140);
    }
}
