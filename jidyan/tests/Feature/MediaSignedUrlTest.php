<?php

use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('generates signed playlist urls and rewrites segment references', function () {
    Storage::fake('media_hls');

    $profile = PlayerProfile::factory()->create(['visibility' => 'public']);

    $media = PlayerMedia::create([
        'player_id' => $profile->id,
        'type' => 'video',
        'provider' => 'local',
        'original_filename' => 'highlight.mp4',
        'path' => 'inbox/highlight.mp4',
        'status' => 'ready',
        'meta' => ['renditions' => ['360p' => []]],
    ]);

    $media->forceFill([
        'hls_path' => 'media/hls/'.$media->id.'/index.m3u8',
        'poster_path' => 'media/hls/'.$media->id.'/poster.jpg',
    ])->save();

    Storage::disk('media_hls')->put($media->id.'/index.m3u8', implode("\n", [
        '#EXTM3U',
        '#EXT-X-STREAM-INF:BANDWIDTH=800000,RESOLUTION=640x360',
        '360p/index.m3u8',
    ]));

    Storage::disk('media_hls')->put($media->id.'/360p/index.m3u8', implode("\n", [
        '#EXTM3U',
        '#EXTINF:4.0,',
        'seg_0001.ts',
        '#EXTINF:4.0,',
        'seg_0002.ts',
    ]));

    Storage::disk('media_hls')->put($media->id.'/360p/seg_0001.ts', 'segment-one');
    Storage::disk('media_hls')->put($media->id.'/360p/seg_0002.ts', 'segment-two');

    $response = $this->getJson(route('media.signed-url', $media));
    $response->assertOk();

    $masterUrl = $response->json('url');

    expect($masterUrl)->toBeString()->toContain('signature=');

    $playlistResponse = $this->get($masterUrl);
    $playlistResponse->assertOk();

    $body = $playlistResponse->getContent();

    expect($body)->toContain('media/hls/'.$media->id.'/360p/index.m3u8')
        ->and($body)->toContain('signature=');

    $segmentLine = Str::of($body)
        ->explode("\n")
        ->first(fn ($line) => Str::contains($line, 'seg_0001.ts'));

    expect($segmentLine)->toBeString()->toContain('signature=');

    $segmentResponse = $this->get($segmentLine);
    $segmentResponse->assertOk();
    expect($segmentResponse->getContent())->toBe('segment-one');
});

it('prevents guests from signing private media', function () {
    Storage::fake('media_hls');

    $profile = PlayerProfile::factory()->create(['visibility' => 'private']);

    $media = PlayerMedia::create([
        'player_id' => $profile->id,
        'type' => 'video',
        'provider' => 'local',
        'original_filename' => 'private.mp4',
        'path' => 'inbox/private.mp4',
        'status' => 'ready',
    ]);

    $media->forceFill([
        'hls_path' => 'media/hls/'.$media->id.'/index.m3u8',
        'poster_path' => 'media/hls/'.$media->id.'/poster.jpg',
    ])->save();

    Storage::disk('media_hls')->put($media->id.'/index.m3u8', '#EXTM3U');

    $this->getJson(route('media.signed-url', $media))->assertForbidden();

    $user = $profile->user;
    $this->actingAs($user);

    $this->getJson(route('media.signed-url', $media))->assertOk();
});
