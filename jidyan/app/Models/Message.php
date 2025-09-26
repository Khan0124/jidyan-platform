<?php

namespace App\Models;

use App\Notifications\NewMessageNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_user_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $message): void {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $message->loadMissing([
                'thread.participants.user',
                'sender',
            ]);

            $thread = $message->thread;

            if (! $thread) {
                return;
            }

            /** @var Collection<int, \App\Models\User> $recipients */
            $recipients = $thread->participants
                ->pluck('user')
                ->filter()
                ->reject(fn (User $user) => $user->is($message->sender))
                ->unique('id');

            if ($recipients->isEmpty()) {
                return;
            }

            Notification::send($recipients, new NewMessageNotification($message));
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }
}
