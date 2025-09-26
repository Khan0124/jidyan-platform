<?php

use App\Models\PlayerProfile;
use App\Models\PlayerStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows player to update profile', function () {
    $user = User::factory()->create();
    $user->assignRole('player');
    $this->actingAs($user);

    $response = $this->put(route('dashboard.player.profile.update'), [
        'dob' => '2006-01-01',
        'nationality' => 'UAE',
        'city' => 'Dubai',
        'country' => 'UAE',
        'height_cm' => 180,
        'weight_kg' => 75,
        'position' => 'CM',
        'preferred_foot' => 'right',
        'visibility' => 'public',
        'availability' => 'available',
    ]);

    $response->assertRedirect(route('dashboard.player.profile.edit'));
    $this->assertDatabaseHas('profiles_players', [
        'user_id' => $user->id,
        'city' => 'Dubai',
    ]);
});

it('allows player to manage season statistics', function () {
    $user = User::factory()->create();
    $user->assignRole('player');
    $this->actingAs($user);

    $this->post(route('dashboard.player.stats.store'), [
        'season' => '2022/2023',
        'matches' => 18,
        'goals' => 7,
        'assists' => 5,
        'notes' => 'League and cup combined.',
    ])->assertRedirect();

    $stat = PlayerStat::first();

    expect($stat)->not->toBeNull();

    $this->assertDatabaseHas('player_stats', [
        'player_id' => $user->playerProfile->id,
        'season' => '2022/2023',
    ]);

    $this->put(route('dashboard.player.stats.update', $stat), [
        'season' => '2022/2023',
        'matches' => 20,
        'goals' => 9,
        'assists' => 6,
        'notes' => 'Updated stats.',
    ])->assertRedirect();

    $this->assertDatabaseHas('player_stats', [
        'id' => $stat->id,
        'matches' => 20,
        'goals' => 9,
    ]);

    $this->delete(route('dashboard.player.stats.destroy', $stat))->assertRedirect();

    $this->assertDatabaseMissing('player_stats', [
        'id' => $stat->id,
    ]);
});

it('records profile views in the activity log', function () {
    $player = PlayerProfile::factory()->create(['visibility' => 'public']);
    $viewer = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('players.show', $player))
        ->assertOk();

    $this->assertDatabaseHas('view_logs', [
        'player_id' => $player->id,
        'viewer_user_id' => $viewer->id,
    ]);
});
