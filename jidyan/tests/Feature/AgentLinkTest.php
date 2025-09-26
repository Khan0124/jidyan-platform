<?php

use App\Models\Agent;
use App\Models\PlayerAgentLink;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a player to request an agent link and refresh an existing relationship', function () {
    $player = User::factory()->create();
    $player->assignRole('player');
    PlayerProfile::factory()->for($player, 'user')->create();

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agent');
    $agent = Agent::create([
        'user_id' => $agentUser->id,
        'license_no' => 'AG-001',
    ]);

    $this->from(route('dashboard.player.profile.edit'))
        ->actingAs($player)
        ->post(route('player.agent-links.store'), [
            'agent_id' => $agent->id,
        ])
        ->assertRedirect();

    $link = PlayerAgentLink::first();

    expect($link)->not->toBeNull();
    expect($link->status)->toBe('pending');
    expect($link->requested_by)->toBe($player->id);

    $link->update([
        'status' => 'revoked',
        'responded_at' => now(),
    ]);

    $this->from(route('dashboard.player.profile.edit'))
        ->actingAs($player)
        ->post(route('player.agent-links.store'), [
            'agent_id' => $agent->id,
        ])
        ->assertRedirect();

    $link->refresh();

    expect(PlayerAgentLink::count())->toBe(1);
    expect($link->status)->toBe('pending');
    expect($link->responded_at)->toBeNull();
    expect($link->requested_by)->toBe($player->id);
});

it('enforces approval permissions for agent links', function () {
    $player = User::factory()->create();
    $player->assignRole('player');
    PlayerProfile::factory()->for($player, 'user')->create();

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agent');
    $agent = Agent::create([
        'user_id' => $agentUser->id,
        'license_no' => 'AG-002',
    ]);

    $link = PlayerAgentLink::create([
        'player_id' => $player->playerProfile->id,
        'agent_id' => $agent->id,
        'status' => 'pending',
        'requested_by' => $player->id,
    ]);

    $coach = User::factory()->create();
    $coach->assignRole('coach');

    $this->actingAs($coach)
        ->patch(route('player.agent-links.update', $link), [
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->from('/dashboard')
        ->actingAs($agentUser)
        ->patch(route('player.agent-links.update', $link), [
            'status' => 'active',
        ])
        ->assertRedirect();

    $link->refresh();

    expect($link->status)->toBe('active');
    expect($link->responded_at)->not->toBeNull();

    $this->actingAs($player)
        ->patch(route('player.agent-links.update', $link), [
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->from(route('dashboard.player.profile.edit'))
        ->actingAs($player)
        ->patch(route('player.agent-links.update', $link), [
            'status' => 'revoked',
        ])
        ->assertRedirect();

    $link->refresh();

    expect($link->status)->toBe('revoked');
    expect($link->responded_at)->not->toBeNull();
});
