<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

test('google redirect works', function () {
    $response = $this->get('/auth/google');

    $response->assertRedirectContains('accounts.google.com');
});

test('google callback creates new user', function () {
    // Mock the Socialite user
    $googleUser = (object) [
        'id' => '1234567890',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    // Mock the provider
    $provider = $this->mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    // Mock Socialite facade
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    // Ensure user doesn't exist initially
    expect(User::where('email', 'john@example.com')->exists())->toBeFalse();

    $response = $this->get('/auth/google/callback');

    // User should now exist and be authenticated
    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // Check user has Google ID
    $user = User::where('email', 'john@example.com')->first();
    expect($user->google_id)->toBe('1234567890');
    expect($user->name)->toBe('John Doe');
    expect($user->email_verified_at)->not->toBeNull();
});

test('google callback logs in existing user with google id', function () {
    // Create user with Google ID
    $user = User::factory()->create([
        'google_id' => '1234567890',
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    $googleUser = (object) [
        'id' => '1234567890',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $provider = $this->mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    // User should be authenticated
    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('google callback updates existing user without google id', function () {
    // Create user without Google ID
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'google_id' => null,
    ]);

    $googleUser = (object) [
        'id' => '1234567890',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $provider = $this->mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    // User should be authenticated and have Google ID updated
    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();
    expect($user->google_id)->toBe('1234567890');
});

test('google callback handles errors gracefully', function () {
    Socialite::shouldReceive('driver')
        ->with('google')
        ->andThrow(new Exception('OAuth error'));

    $response = $this->get('/auth/google/callback');

    $this->assertGuest();
    $response->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Something went wrong. Please try again.');
});
