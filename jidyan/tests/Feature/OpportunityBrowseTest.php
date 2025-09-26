<?php

use App\Models\Opportunity;
use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays only public published opportunities', function () {
    $visible = Opportunity::factory()->create(['title' => 'Dubai Open Trial']);
    Opportunity::factory()->draft()->create(['title' => 'Draft Only']);
    Opportunity::factory()->privateVisibility()->create(['title' => 'Private Trial']);

    $response = $this->get(route('opportunities.index'));

    $response->assertOk();
    $response->assertSee('Dubai Open Trial');
    $response->assertDontSee('Draft Only');
    $response->assertDontSee('Private Trial');
});

it('filters opportunities by country and deadline range', function () {
    $match = Opportunity::factory()->create([
        'title' => 'Riyadh Selection Camp',
        'location_country' => 'Saudi Arabia',
        'deadline_at' => now()->addDays(5),
    ]);

    Opportunity::factory()->create([
        'title' => 'Doha Friendly',
        'location_country' => 'Qatar',
        'deadline_at' => now()->addDays(20),
    ]);

    $response = $this->get(route('opportunities.index', [
        'country' => 'Saudi Arabia',
        'deadline_to' => now()->addDays(7)->toDateString(),
    ]));

    $response->assertOk();
    $response->assertSee('Riyadh Selection Camp');
    $response->assertDontSee('Doha Friendly');
});

it('returns opportunities as json when requested', function () {
    Opportunity::factory()->count(2)->create();

    $response = $this->getJson(route('opportunities.index'));

    $response->assertOk()->assertJsonStructure(['data' => [['id', 'title', 'description', 'deadline_at']]]);
});

it('shows opportunity detail page for public records', function () {
    $opportunity = Opportunity::factory()->create([
        'title' => 'Abu Dhabi Showcase',
        'requirements' => [
            ['label' => 'Age', 'value' => '16-20'],
        ],
    ]);

    $response = $this->get(route('opportunities.show', $opportunity));

    $response->assertOk();
    $response->assertSee('Abu Dhabi Showcase');
    $response->assertSee('16-20');
});

it('hides private opportunities from public view', function () {
    $opportunity = Opportunity::factory()->privateVisibility()->create([
        'title' => 'Hidden Camp',
    ]);

    $this->get(route('opportunities.show', $opportunity))->assertNotFound();
});

it('allows players to submit an application from the public page', function () {
    $opportunity = Opportunity::factory()->create();

    $user = User::factory()->create();
    $user->assignRole('player');
    PlayerProfile::factory()->for($user, 'user')->create();

    $this->actingAs($user);

    $response = $this->post(route('opportunities.apply', $opportunity), [
        'note' => 'Ready to compete',
    ]);

    $response->assertRedirect(route('opportunities.show', $opportunity));

    $this->assertDatabaseHas('applications', [
        'opportunity_id' => $opportunity->id,
        'player_id' => $user->playerProfile->id,
        'note' => 'Ready to compete',
    ]);
});
