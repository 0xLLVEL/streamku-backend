<?php

use App\Models\Movie;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can fetch their watchlist', function () {
    $user = User::factory()->create();
    Watchlist::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson(route('watchlist.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can add movie to watchlist', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson(route('watchlist.store'), [
        'media_id' => $movie->id,
        'media_type' => 'movie',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('watchlists', [
        'user_id' => $user->id,
        'watchlistable_id' => $movie->id,
        'watchlistable_type' => Movie::class,
    ]);
});

test('user can remove item from watchlist', function () {
    $user = User::factory()->create();
    $watchlist = Watchlist::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson(route('watchlist.destroy', $watchlist));

    $response->assertOk();
    $this->assertDatabaseMissing('watchlists', ['id' => $watchlist->id]);
});
