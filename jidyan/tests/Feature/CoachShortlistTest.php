<?php

use App\Livewire\Coach\PlayerSearch as PlayerSearchComponent;
use App\Models\Coach;
use App\Models\PlayerProfile;
use App\Models\Shortlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows coaches to create shortlists and attach players', function () {
    $coachUser = User::factory()->create();
    $coachUser->assignRole('coach');
    Coach::create(['user_id' => $coachUser->id]);

    $player = PlayerProfile::factory()->create([
        'visibility' => 'public',
        'position' => 'FW',
    ]);

    Livewire::actingAs($coachUser)
        ->test(PlayerSearchComponent::class)
        ->set('newShortlistTitle', 'U18 Targets')
        ->call('createShortlist')
        ->assertSet('newShortlistTitle', '')
        ->assertSet('selectedShortlistId', Shortlist::first()->id);

    $shortlist = Shortlist::first();

    Livewire::actingAs($coachUser)
        ->test(PlayerSearchComponent::class, ['shortlist' => $shortlist->id])
        ->set("notes.{$player->id}", 'Strong finisher with pace')
        ->call('addToShortlist', $player->id);

    $this->assertDatabaseHas('shortlist_items', [
        'shortlist_id' => $shortlist->id,
        'player_id' => $player->id,
        'note' => 'Strong finisher with pace',
    ]);
});

it('allows coaches to remove players from shortlists', function () {
    $coachUser = User::factory()->create();
    $coachUser->assignRole('coach');
    $coach = Coach::create(['user_id' => $coachUser->id]);

    $player = PlayerProfile::factory()->create(['visibility' => 'public']);
    $shortlist = $coach->shortlists()->create(['title' => 'Watchlist']);
    $shortlist->items()->create(['player_id' => $player->id, 'note' => 'Initial note']);

    Livewire::actingAs($coachUser)
        ->test(PlayerSearchComponent::class, ['shortlist' => $shortlist->id])
        ->call('removeFromShortlist', $player->id);

    $this->assertDatabaseMissing('shortlist_items', [
        'shortlist_id' => $shortlist->id,
        'player_id' => $player->id,
    ]);
});
