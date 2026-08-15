<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson(route('auth.register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
    ]);

    $response = $this->postJson(route('auth.login'), [
        'email' => 'login@example.com',
        'password' => 'password', // Default factory password
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['user' => ['id', 'email'], 'token']]);
});

test('user can get their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('auth.me'));

    $response->assertOk()
        ->assertJsonPath('data.user.email', $user->email);
});

test('user can logout', function () {
    $user = User::factory()->create([
        'email' => 'logout@example.com',
    ]);

    // Login to get a real token
    $loginResponse = $this->postJson(route('auth.login'), [
        'email' => 'logout@example.com',
        'password' => 'password',
    ]);

    $token = $loginResponse->json('data.token');

    $response = $this->withToken($token)->postJson(route('auth.logout'));

    $response->assertOk();
});

test('unauthenticated users cannot access protected routes', function () {
    $response = $this->getJson(route('auth.me'));
    $response->assertUnauthorized();
});
