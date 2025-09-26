<?php

use App\Jobs\ProcessMediaUploadJob;
use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('assembles chunked uploads and dispatches processing', function () {
    Storage::fake('media_inbox');
    Queue::fake();

    $user = User::factory()->create();
    $user->assignRole('player');
    PlayerProfile::factory()->create(['user_id' => $user->id]);

    $uuid = Str::uuid()->toString();

    $this->actingAs($user);

    $this->postJson(route('media.store'), [
        'file' => UploadedFile::fake()->createWithContent('chunk1.part', str_repeat('A', 1024)),
        'chunk_total' => 2,
        'chunk_index' => 0,
        'upload_uuid' => $uuid,
        'filename' => 'highlight.mp4',
    ])->assertStatus(202)->assertJson(['status' => 'chunk_received']);

    $this->postJson(route('media.store'), [
        'file' => UploadedFile::fake()->createWithContent('chunk2.part', str_repeat('B', 1024)),
        'chunk_total' => 2,
        'chunk_index' => 1,
        'upload_uuid' => $uuid,
        'filename' => 'highlight.mp4',
    ])->assertStatus(201)->assertJsonStructure(['data' => ['id', 'status', 'original_filename']]);

    $media = PlayerMedia::first();

    expect($media)->not->toBeNull();
    expect($media->original_filename)->toBe('highlight.mp4');
    Storage::disk('media_inbox')->assertExists($media->path);

    Queue::assertPushed(ProcessMediaUploadJob::class, function ($job) use ($media) {
        return $job->mediaId === $media->id;
    });
});
