<?php

use App\Models\Episode;
use App\Models\Season;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can list tv shows', function () {
    TvShow::factory()->count(10)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('tv-shows.index'));

    $response->assertOk()
        ->assertJsonCount(10, 'data');
});

test('users can view tv show details', function () {
    $tvShow = TvShow::factory()->create(['name' => 'Test TV Show']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('tv-shows.show', $tvShow));

    $response->assertOk()
        ->assertJsonPath('data.name', 'Test TV Show');
});

test('users can view a season', function () {
    $tvShow = TvShow::factory()->create();
    $season = Season::factory()->create(['tv_show_id' => $tvShow->id, 'season_number' => 1]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('tv-shows.seasons.show', [$tvShow, $season->season_number]));

    $response->assertOk()
        ->assertJsonPath('data.season_number', 1);
});

test('users can view an episode', function () {
    $tvShow = TvShow::factory()->create();
    $season = Season::factory()->create(['tv_show_id' => $tvShow->id, 'season_number' => 1]);
    $episode = Episode::factory()->create(['season_id' => $season->id, 'episode_number' => 5]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('tv-shows.seasons.episodes.show', [$tvShow, $season->season_number, $episode->episode_number]));

    $response->assertOk()
        ->assertJsonPath('data.episode_number', 5);
});
