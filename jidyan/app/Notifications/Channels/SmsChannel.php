<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('sms');

        if (! $to) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (blank($message)) {
            return;
        }

        $driver = config('services.sms.driver', 'log');

        if ($driver !== 'log') {
            Log::warning('SMS driver not implemented, falling back to log driver.', [
                'driver' => $driver,
                'to' => $to,
            ]);
        }

        $channel = config('services.sms.log_channel', 'stack');

        Log::channel($channel)->info('SMS notification dispatched.', [
            'to' => $to,
            'message' => $message,
        ]);
    }
}
