<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('allows a new player to register with verification notice', function () {
    Event::fake([Registered::class]);

    $response = $this->post('/register', [
        'name' => 'Test Player',
        'email' => 'player@example.com',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
    ]);

    $response->assertRedirect('/dashboard');

    $user = User::where('email', 'player@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole('player'))->toBeTrue();
    expect($user->playerProfile)->not->toBeNull();
    Event::assertDispatched(Registered::class);
});

it('authenticates an existing user', function () {
    $user = User::factory()->create([
        'email' => 'coach@example.com',
        'password' => Hash::make('Secret123!'),
    ]);

    $response = $this->post('/login', [
        'email' => 'coach@example.com',
        'password' => 'Secret123!',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});
