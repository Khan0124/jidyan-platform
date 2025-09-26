<?php

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('allows admins to toggle feature flags via dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $flag = FeatureFlag::factory()->create([
        'enabled' => false,
        'description' => 'Initial flag description',
    ]);

    $this->actingAs($admin)
        ->from(route('feature-flags.index'))
        ->put(route('feature-flags.update', $flag), [
            'enabled' => true,
            'description' => 'Initial flag description',
        ])
        ->assertRedirect(route('feature-flags.index'))
        ->assertSessionHas('status');

    expect($flag->refresh()->enabled)->toBeTrue();
});

it('rejects non-admin users from updating feature flags', function () {
    $coach = User::factory()->create();
    $coach->assignRole('coach');

    $flag = FeatureFlag::factory()->create();

    $this->actingAs($coach)
        ->put(route('feature-flags.update', $flag), [
            'enabled' => true,
        ])
        ->assertForbidden();
});

it('exposes feature flag management via the api for admins', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $flag = FeatureFlag::factory()->create([
        'key' => 'media.chunked_uploads',
        'enabled' => false,
    ]);

    expect(FeatureFlag::isEnabled('media.chunked_uploads'))->toBeFalse();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/feature-flags')
        ->assertOk()
        ->assertJsonPath('data.0.key', $flag->key);

    Cache::forget('feature_flags:media.chunked_uploads');
    FeatureFlag::isEnabled('media.chunked_uploads');

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/v1/admin/feature-flags/{$flag->id}", [
            'enabled' => true,
            'description' => $flag->description,
        ])
        ->assertOk()
        ->assertJsonPath('data.enabled', true);

    expect(FeatureFlag::isEnabled('media.chunked_uploads'))->toBeTrue();
});
