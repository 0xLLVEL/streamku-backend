<?php

use App\Events\WatchPartySynced;
use App\Models\Movie;
use App\Models\User;
use App\Models\WatchParty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('authenticated user can create a watch party', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/watch-parties', [
        'media_type' => 'movie',
        'media_id' => $movie->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.host_id', $user->id)
        ->assertJsonPath('data.media_type', 'movie')
        ->assertJsonPath('data.media_id', $movie->id);

    $this->assertDatabaseHas('watch_parties', [
        'host_id' => $user->id,
        'mediable_id' => $movie->id,
    ]);
});

test('authenticated user can join a watch party', function () {
    $host = User::factory()->create();
    $friend = User::factory()->create();
    $movie = Movie::factory()->create();

    $party = WatchParty::create([
        'host_id' => $host->id,
        'mediable_type' => Movie::class,
        'mediable_id' => $movie->id,
    ]);
    $party->members()->attach($host->id);

    $response = $this->actingAs($friend)->postJson("/api/v1/watch-parties/{$party->id}/join");

    $response->assertStatus(200)->assertJson(['message' => 'Joined successfully']);

    $this->assertDatabaseHas('watch_party_users', [
        'watch_party_id' => $party->id,
        'user_id' => $friend->id,
    ]);
});

test('host can sync playback and broadcast event', function () {
    Event::fake();

    $host = User::factory()->create();
    $movie = Movie::factory()->create();

    $party = WatchParty::create([
        'host_id' => $host->id,
        'mediable_type' => Movie::class,
        'mediable_id' => $movie->id,
    ]);
    $party->members()->attach($host->id);

    $response = $this->actingAs($host)->postJson("/api/v1/watch-parties/{$party->id}/sync", [
        'is_playing' => true,
        'current_time' => 120.5,
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(WatchPartySynced::class, function ($event) use ($party) {
        return $event->watchPartyId === $party->id
            && $event->isPlaying === true
            && $event->currentTime === 120.5;
    });
});

test('non-host cannot sync playback', function () {
    Event::fake();

    $host = User::factory()->create();
    $friend = User::factory()->create();
    $movie = Movie::factory()->create();

    $party = WatchParty::create([
        'host_id' => $host->id,
        'mediable_type' => Movie::class,
        'mediable_id' => $movie->id,
    ]);
    $party->members()->attach([$host->id, $friend->id]);

    $response = $this->actingAs($friend)->postJson("/api/v1/watch-parties/{$party->id}/sync", [
        'is_playing' => true,
        'current_time' => 120.5,
    ]);

    $response->assertStatus(403);
    Event::assertNotDispatched(WatchPartySynced::class);
});
