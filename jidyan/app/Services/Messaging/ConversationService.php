<?php

namespace App\Services\Messaging;

use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Find an existing two-party thread or create a new one.
     *
     * @return array{0: MessageThread, 1: bool} Tuple of thread and creation flag.
     */
    public function findOrCreateThread(User $actor, User $recipient, ?string $subject = null): array
    {
        $existing = $this->findExistingThread($actor, $recipient);

        if ($existing) {
            return [$existing, false];
        }

        $thread = DB::transaction(function () use ($actor, $recipient, $subject) {
            $thread = MessageThread::create([
                'subject' => $subject,
            ]);

            $participants = collect([$actor, $recipient])->unique('id');

            $participants->each(function (User $user) use ($thread) {
                $thread->participants()->create([
                    'user_id' => $user->id,
                ]);
            });

            return $thread->fresh(['participants.user']);
        });

        return [$thread, true];
    }

    public function markThreadRead(MessageThread $thread, User $user): void
    {
        $thread->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }

    protected function findExistingThread(User $actor, User $recipient): ?MessageThread
    {
        $actorId = $actor->getKey();
        $recipientId = $recipient->getKey();

        return MessageThread::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $actorId))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $recipientId))
            ->whereDoesntHave('participants', fn ($query) => $query->whereNotIn('user_id', [$actorId, $recipientId]))
            ->with(['participants.user'])
            ->orderByDesc('updated_at')
            ->first();
    }
}
