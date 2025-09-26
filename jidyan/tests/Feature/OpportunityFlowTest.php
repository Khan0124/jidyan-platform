<?php

use App\Models\Club;
use App\Models\ClubAdmin;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows club admin to create opportunity', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('club_admin');
    $club = Club::create(['name' => 'Test FC', 'country' => 'UAE', 'city' => 'Dubai']);
    ClubAdmin::create(['user_id' => $adminUser->id, 'club_id' => $club->id]);

    $this->actingAs($adminUser);

    $response = $this->post(route('dashboard.club.opportunities.store'), [
        'title' => 'Trial',
        'description' => 'Trial description',
        'requirements' => [['label' => 'Age', 'value' => '18']],
        'location_city' => 'Dubai',
        'location_country' => 'UAE',
        'deadline_at' => now()->addWeek()->toDateString(),
        'status' => 'published',
        'visibility' => 'public',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('opportunities', ['title' => 'Trial']);
});
