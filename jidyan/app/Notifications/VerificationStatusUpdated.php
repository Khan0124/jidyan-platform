<?php

namespace App\Notifications;

use App\Models\Verification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public Verification $verification)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusKey = match ($this->verification->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending',
        };

        $subject = __('Your :type verification is :status', [
            'type' => __('verification.type.'.$this->verification->type),
            'status' => __($statusKey),
        ]);

        $mail = (new MailMessage())
            ->subject($subject)
            ->line(__('Verification type: :type', ['type' => __('verification.type.'.$this->verification->type)]))
            ->line(__('Current status: :status', ['status' => __($statusKey)]));

        if ($this->verification->reason) {
            $mail->line(__('Reviewer note: :reason', ['reason' => $this->verification->reason]));
        }

        $mail->action(__('View verification requests'), route('verifications.create'));

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'verification_id' => $this->verification->id,
            'type' => $this->verification->type,
            'status' => $this->verification->status,
            'reason' => $this->verification->reason,
        ];
    }
}
