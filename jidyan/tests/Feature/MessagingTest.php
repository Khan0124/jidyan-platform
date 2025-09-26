<?php

use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

it('starts a conversation and reuses the existing thread on subsequent messages', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $this->actingAs($sender);

    $firstResponse = $this->postJson(route('dashboard.messages.start'), [
        'recipient_id' => $recipient->id,
        'body' => 'Hello there',
    ])->assertStatus(201)->assertJsonStructure([
        'thread' => ['id'],
        'message' => ['id', 'body'],
    ]);

    $threadId = $firstResponse->json('thread.id');

    expect(MessageThread::count())->toBe(1);
    expect(MessageThreadParticipant::where('thread_id', $threadId)->count())->toBe(2);
    $this->assertDatabaseHas('messages', [
        'thread_id' => $threadId,
        'body' => 'Hello there',
    ]);

    $secondResponse = $this->postJson(route('dashboard.messages.start'), [
        'recipient_id' => $recipient->id,
        'body' => 'Second message',
    ])->assertStatus(200)->assertJsonPath('thread.id', $threadId);

    expect(MessageThread::count())->toBe(1);
    $this->assertDatabaseHas('messages', [
        'thread_id' => $threadId,
        'body' => 'Second message',
    ]);
});

it('prevents users outside the thread from replying', function () {
    $thread = MessageThread::create(['subject' => 'Private thread']);
    $participant = User::factory()->create();
    $intruder = User::factory()->create();

    $thread->participants()->create(['user_id' => $participant->id]);

    $this->actingAs($intruder)
        ->postJson(route('dashboard.messages.store', $thread), ['body' => 'I should not post'])
        ->assertStatus(403);
});

it('allows thread participants to reply and updates read markers', function () {
    $thread = MessageThread::create(['subject' => 'Chat']);
    $author = User::factory()->create();
    $other = User::factory()->create();

    $authorParticipant = $thread->participants()->create(['user_id' => $author->id]);
    $thread->participants()->create(['user_id' => $other->id]);

    $this->actingAs($author)
        ->postJson(route('dashboard.messages.store', $thread), ['body' => 'Reply body'])
        ->assertStatus(201)
        ->assertJsonPath('message.body', 'Reply body');

    $thread->refresh();
    $authorParticipant->refresh();

    $this->assertDatabaseHas('messages', [
        'thread_id' => $thread->id,
        'body' => 'Reply body',
    ]);

    expect($authorParticipant->last_read_at)->not()->toBeNull();
    expect($thread->updated_at)->toBeGreaterThan($thread->created_at);
});

it('notifies other participants when a message is posted', function () {
    Notification::fake();
    config(['services.sms.driver' => 'log']);

    $thread = MessageThread::create(['subject' => 'Ping']);
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['phone' => '+971500000001']);

    $thread->participants()->create(['user_id' => $sender->id]);
    $thread->participants()->create(['user_id' => $recipient->id]);

    $this->actingAs($sender)
        ->postJson(route('dashboard.messages.store', $thread), ['body' => 'Hello from the pitch'])
        ->assertStatus(201);

    Notification::assertSentTo(
        $recipient,
        NewMessageNotification::class,
        function ($notification, array $channels) use ($sender) {
            expect($channels)->toContain('mail');
            expect($channels)->toContain('database');
            expect($channels)->toContain(SmsChannel::class);
            expect($notification->message->sender->is($sender))->toBeTrue();

            return true;
        }
    );

    Notification::assertNotSentTo($sender, NewMessageNotification::class);
});
