<?php

use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters players with ready video clips via api', function () {
    $withVideo = PlayerProfile::factory()->create([
        'visibility' => 'public',
        'position' => 'FW',
        'dob' => now()->subYears(20),
        'availability' => 'available',
        'last_active_at' => now()->subDays(2),
    ]);

    PlayerMedia::create([
        'player_id' => $withVideo->id,
        'type' => 'video',
        'provider' => 'local',
        'original_filename' => 'clip.mp4',
        'path' => 'inbox/clip.mp4',
        'status' => 'ready',
        'hls_path' => 'media/hls/sample/index.m3u8',
        'poster_path' => 'media/hls/sample/poster.jpg',
    ]);

    PlayerProfile::factory()->create([
        'visibility' => 'public',
        'position' => 'FW',
        'dob' => now()->subYears(18),
    ]);

    $response = $this->getJson('/api/v1/players?position=FW&has_video=1&min_age=19&max_age=22');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect(collect($response->json('data'))->pluck('id'))->toContain($withVideo->id);
});

it('filters by availability and recent activity', function () {
    $recent = PlayerProfile::factory()->create([
        'visibility' => 'public',
        'availability' => 'available',
        'last_active_at' => now()->subDays(3),
    ]);

    PlayerProfile::factory()->create([
        'visibility' => 'public',
        'availability' => 'contracted',
        'last_active_at' => now()->subDays(45),
    ]);

    $response = $this->getJson('/api/v1/players?availability=available&last_active=7');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($recent->id);
});

it('searches by keywords across player profiles', function () {
    $targetUser = User::factory()->create(['name' => 'Khalid Al Harbi']);

    $match = PlayerProfile::factory()->create([
        'user_id' => $targetUser->id,
        'visibility' => 'public',
        'position' => 'RW',
        'city' => 'Jeddah',
        'country' => 'Saudi Arabia',
        'current_club' => 'Jeddah Youth',
        'bio' => 'Explosive winger with pace and accurate crosses.',
    ]);

    PlayerProfile::factory()->create([
        'visibility' => 'public',
        'position' => 'GK',
        'bio' => 'Shot-stopping goalkeeper with strong aerial presence.',
    ]);

    $response = $this->getJson('/api/v1/players?keywords=winger%20Jeddah');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($match->id);
    expect($response->json('data.0'))->toHaveKey('relevance');
});
