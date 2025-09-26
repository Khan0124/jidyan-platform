<?php

use App\Models\ContentReport;
use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows a player to submit and update a pending report without duplicates', function () {
    $reporter = User::factory()->create();
    $reporter->assignRole('player');

    $media = PlayerMedia::factory()
        ->for(PlayerProfile::factory(), 'player')
        ->create();

    $this->actingAs($reporter)
        ->post(route('reports.store'), [
            'reportable_type' => 'player_media',
            'reportable_id' => $media->id,
            'reason' => 'Inappropriate content',
            'description' => 'Contains offensive language',
        ])
        ->assertRedirect();

    $this->actingAs($reporter)
        ->post(route('reports.store'), [
            'reportable_type' => 'player_media',
            'reportable_id' => $media->id,
            'reason' => 'Inappropriate content',
            'description' => 'Additional context provided',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('content_reports', [
        'reportable_type' => PlayerMedia::class,
        'reportable_id' => $media->id,
        'reporter_user_id' => $reporter->id,
        'status' => ContentReport::STATUS_PENDING,
        'description' => 'Additional context provided',
    ]);

    expect(ContentReport::count())->toBe(1);
});

it('allows verifiers to resolve reports from the dashboard', function () {
    $reporter = User::factory()->create();
    $reporter->assignRole('player');

    $verifier = User::factory()->create();
    $verifier->assignRole('verifier');

    $media = PlayerMedia::factory()->for(PlayerProfile::factory(), 'player')->create();

    $report = ContentReport::create([
        'reportable_type' => PlayerMedia::class,
        'reportable_id' => $media->id,
        'reporter_user_id' => $reporter->id,
        'reason' => 'Spam',
        'description' => 'Repeated advertisement',
        'status' => ContentReport::STATUS_PENDING,
    ]);

    $this->actingAs($verifier)
        ->patch(route('reports.update', $report), [
            'status' => ContentReport::STATUS_RESOLVED,
            'resolution_notes' => 'Video removed and player notified.',
        ])
        ->assertRedirect(route('reports.index'));

    $this->assertDatabaseHas('content_reports', [
        'id' => $report->id,
        'status' => ContentReport::STATUS_RESOLVED,
        'resolved_by' => $verifier->id,
    ]);
});

it('exposes moderation APIs to verifiers', function () {
    $reporter = User::factory()->create();
    $reporter->assignRole('player');
    $media = PlayerMedia::factory()->for(PlayerProfile::factory(), 'player')->create();

    $report = ContentReport::create([
        'reportable_type' => PlayerMedia::class,
        'reportable_id' => $media->id,
        'reporter_user_id' => $reporter->id,
        'reason' => 'Spam',
        'status' => ContentReport::STATUS_PENDING,
    ]);

    $verifier = User::factory()->create();
    $verifier->assignRole('verifier');

    $this->actingAs($verifier, 'sanctum')
        ->getJson('/api/v1/reports')
        ->assertOk()
        ->assertJsonFragment(['id' => $report->id]);

    $this->actingAs($verifier, 'sanctum')
        ->patchJson("/api/v1/reports/{$report->id}", [
            'status' => ContentReport::STATUS_DISMISSED,
            'resolution_notes' => 'Reviewed and acceptable.',
        ])
        ->assertOk()
        ->assertJsonFragment(['status' => ContentReport::STATUS_DISMISSED]);
});
