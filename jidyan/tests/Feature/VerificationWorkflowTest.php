<?php

use App\Models\PlayerProfile;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\VerificationStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows players to submit verification requests with supporting documents', function () {
    Storage::fake('secure_documents');

    $player = User::factory()->create();
    $player->assignRole('player');
    PlayerProfile::factory()->create(['user_id' => $player->id]);

    $file = UploadedFile::fake()->create('passport.pdf', 500, 'application/pdf');

    $response = $this->actingAs($player)
        ->post(route('verifications.store'), [
            'type' => 'identity',
            'document' => $file,
        ]);

    $response->assertRedirect();

    $verification = Verification::first();
    expect($verification)->not->toBeNull();
    expect($verification->status)->toBe('pending');
    expect($verification->document_name)->toBe('passport.pdf');

    Storage::disk('secure_documents')->assertExists($verification->document_path);
});

it('approving identity verification awards the badge and notifies the player', function () {
    Notification::fake();

    $player = User::factory()->create();
    $player->assignRole('player');
    $profile = PlayerProfile::factory()->create(['user_id' => $player->id]);

    $verifier = User::factory()->create();
    $verifier->assignRole('verifier');

    $verification = Verification::create([
        'user_id' => $player->id,
        'type' => 'identity',
        'document_path' => 'verifications/'.$player->id.'/passport.pdf',
        'document_name' => 'passport.pdf',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($verifier)
        ->put(route('verifications.update', $verification), [
            'status' => 'approved',
        ]);

    $response->assertRedirect();

    expect($profile->fresh()->verified_identity_at)->not->toBeNull();

    Notification::assertSentTo(
        $player,
        VerificationStatusUpdated::class,
        fn (VerificationStatusUpdated $notification) => $notification->verification->is($verification->fresh())
    );
});

it('rejected verification clears badges and records reviewer reason', function () {
    $player = User::factory()->create();
    $player->assignRole('player');
    $profile = PlayerProfile::factory()->create([
        'user_id' => $player->id,
        'verified_identity_at' => now()->subWeek(),
    ]);

    $verifier = User::factory()->create();
    $verifier->assignRole('verifier');

    $verification = Verification::create([
        'user_id' => $player->id,
        'type' => 'identity',
        'document_path' => 'verifications/'.$player->id.'/passport.pdf',
        'document_name' => 'passport.pdf',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($verifier)
        ->put(route('verifications.update', $verification), [
            'status' => 'rejected',
            'reason' => 'Document expired',
        ]);

    $response->assertRedirect();

    expect($profile->fresh()->verified_identity_at)->toBeNull();
    expect($verification->fresh()->reason)->toBe('Document expired');
});
