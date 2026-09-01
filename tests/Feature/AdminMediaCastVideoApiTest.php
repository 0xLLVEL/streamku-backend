<?php

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('movie video routes bind', function () {
    $movie = Movie::factory()->create();

    $this->actingAs($this->admin)->getJson(route('admin.movies.videos.index', $movie))->assertOk();
    $res = $this->actingAs($this->admin)->postJson(route('admin.movies.videos.store', $movie), [
        'key' => 'abc123def45', 'site' => 'YouTube', 'name' => 'Trailer',
    ]);
    $res->assertCreated();
    $this->assertDatabaseHas('videos', ['key' => 'abc123def45']);

    $video = $movie->videos()->first();
    $this->actingAs($this->admin)->deleteJson(route('admin.movies.videos.destroy', [$movie, $video]))->assertOk();
    $this->assertDatabaseMissing('videos', ['id' => $video->id]);
});

test('tv-show video routes bind', function () {
    $tv = TvShow::factory()->create();

    $this->actingAs($this->admin)->getJson(route('admin.tv-shows.videos.index', $tv))->assertOk();
    $this->actingAs($this->admin)->postJson(route('admin.tv-shows.videos.store', $tv), [
        'key' => 'xyz987zzz21', 'site' => 'YouTube', 'name' => 'Trailer',
    ])->assertCreated();
});

test('episode video routes bind via nested params', function () {
    $tv = TvShow::factory()->create();
    $season = Season::factory()->create(['tv_show_id' => $tv->id, 'season_number' => 1]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'episode_number' => 3,
    ]);

    $url = '/api/v1/admin/tv-shows/'.$tv->id.'/seasons/1/episodes/3/videos';
    $this->actingAs($this->admin)->getJson($url)->assertOk();
    $res = $this->actingAs($this->admin)->postJson($url, [
        'key' => 'abc11122233', 'site' => 'VidKing', 'name' => 'Stream',
    ]);
    $res->assertCreated();
    $this->assertDatabaseHas('videos', ['key' => 'abc11122233', 'videoable_id' => $episode->id]);

    $videoId = $res->json('data.id');
    $this->actingAs($this->admin)->putJson($url.'/'.$videoId, [
        'key' => 'abc11122233', 'site' => 'VidKing', 'name' => 'Updated Stream',
    ])->assertOk();
    $this->actingAs($this->admin)->deleteJson($url.'/'.$videoId)->assertOk();
    $this->assertDatabaseMissing('videos', ['id' => $videoId]);
});

test('movie and tv cast routes bind', function () {
    $movie = Movie::factory()->create();
    $tv = TvShow::factory()->create();

    $this->actingAs($this->admin)->postJson(route('admin.movies.cast.store', $movie), [
        'name' => 'Actor One', 'character' => 'Hero', 'order' => 0,
    ])->assertCreated();

    $mv = $this->actingAs($this->admin)->postJson(route('admin.tv-shows.cast.store', $tv), [
        'name' => 'Actor Two', 'character' => 'Villain', 'order' => 1,
    ]);
    $mv->assertCreated();

    $castId = $mv->json('data.id');
    $this->actingAs($this->admin)->getJson(route('admin.tv-shows.cast.index', $tv))->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($this->admin)->putJson(route('admin.tv-shows.cast.update', [$tv, $castId]), [
        'name' => 'Actor Two', 'character' => 'Hero II', 'order' => 1,
    ])->assertOk();
    $this->actingAs($this->admin)->deleteJson(route('admin.tv-shows.cast.destroy', [$tv, $castId]))->assertOk();
    $this->assertDatabaseMissing('casts', ['id' => $castId]);
});
