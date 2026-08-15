<?php

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can list movies', function () {
    Movie::factory()->count(15)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('movies.index'));

    $response->assertOk()
        ->assertJsonCount(15, 'data');
});

test('users can view movie details', function () {
    $movie = Movie::factory()->create(['title' => 'Test Movie']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('movies.show', $movie));

    $response->assertOk()
        ->assertJsonPath('data.title', 'Test Movie');
});
