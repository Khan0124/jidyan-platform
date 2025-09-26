<?php

use App\Livewire\Club\OpportunityPipeline;
use App\Models\Club;
use App\Models\ClubAdmin;
use App\Models\Opportunity;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows club admins to move applications through the pipeline', function () {
    $user = User::factory()->create();
    $user->assignRole('club_admin');

    $club = Club::create([
        'name' => 'Pipeline FC',
        'country' => 'UAE',
        'city' => 'Dubai',
    ]);

    ClubAdmin::create([
        'user_id' => $user->id,
        'club_id' => $club->id,
        'role_title' => 'Head Scout',
    ]);

    $opportunity = $club->opportunities()->create([
        'title' => 'Midfielder Trials',
        'description' => 'Looking for a creative midfielder.',
        'requirements' => [['label' => 'Age', 'value' => '16-19']],
        'location_city' => 'Dubai',
        'location_country' => 'UAE',
        'deadline_at' => now()->addWeek(),
        'status' => 'published',
        'visibility' => 'private',
    ]);

    $player = PlayerProfile::factory()->create();
    $application = $opportunity->applications()->create([
        'player_id' => $player->id,
        'status' => 'received',
    ]);

    Livewire::actingAs($user)
        ->test(OpportunityPipeline::class, ['opportunity' => $opportunity])
        ->call('moveTo', $application->id, 'shortlisted')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'shortlisted',
        'reviewed_by_user_id' => $user->id,
    ]);
});

it('prevents users from other clubs from modifying the pipeline', function () {
    $club = Club::create([
        'name' => 'Origin Club',
        'country' => 'UAE',
        'city' => 'Sharjah',
    ]);

    $opportunity = $club->opportunities()->create([
        'title' => 'Goalkeeper Trials',
        'description' => 'Shot stopper wanted.',
        'requirements' => [['label' => 'Height', 'value' => '185cm+']],
        'location_city' => 'Sharjah',
        'location_country' => 'UAE',
        'deadline_at' => now()->addDays(10),
        'status' => 'published',
        'visibility' => 'private',
    ]);

    $player = PlayerProfile::factory()->create();
    $application = $opportunity->applications()->create([
        'player_id' => $player->id,
        'status' => 'received',
    ]);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole('club_admin');

    $otherClub = Club::create([
        'name' => 'Rival FC',
        'country' => 'UAE',
        'city' => 'Al Ain',
    ]);

    ClubAdmin::create([
        'user_id' => $otherAdmin->id,
        'club_id' => $otherClub->id,
        'role_title' => 'Recruiter',
    ]);

    Livewire::actingAs($otherAdmin)
        ->test(OpportunityPipeline::class, ['opportunity' => $opportunity])
        ->call('moveTo', $application->id, 'shortlisted')
        ->assertForbidden();

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'received',
    ]);
});
