<?php

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can list genres', function () {
    Genre::factory()->count(5)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('genres.index'));

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('users can view a genre', function () {
    $genre = Genre::factory()->create(['name' => 'Action']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('genres.show', $genre));

    $response->assertOk()
        ->assertJsonPath('data.name', 'Action');
});
