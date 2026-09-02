<?php

use App\Models\Movie;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can list watch history', function () {
    $user = User::factory()->create();
    WatchHistory::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('history.index'));

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('user can store watch history progress', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson(route('history.store'), [
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'progress_seconds' => 120,
        'duration_seconds' => 3600,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('watch_histories', [
        'user_id' => $user->id,
        'watchable_id' => $movie->id,
        'watchable_type' => Movie::class,
        'progress_seconds' => 120,
        'completed' => false,
    ]);
});

test('user can get continue watching list', function () {
    $user = User::factory()->create();
    WatchHistory::factory()->create([
        'user_id' => $user->id,
        'progress_seconds' => 100,
        'duration_seconds' => 3600,
        'completed' => false,
    ]);

    $response = $this->actingAs($user)->getJson(route('history.continue'));

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});
