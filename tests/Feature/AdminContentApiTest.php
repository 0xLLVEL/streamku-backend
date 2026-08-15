<?php

use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create(['is_admin' => false]);
});

test('non-admins cannot access admin routes', function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.movies.index'));
    $response->assertForbidden();
});

test('admin can create a genre', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.genres.store'), [
        'name' => 'Horror',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('genres', ['name' => 'Horror']);
});

test('admin can create a movie', function () {
    $data = Movie::factory()->make(['title' => 'New Admin Movie'])->toArray();
    $response = $this->actingAs($this->admin)->postJson(route('admin.movies.store'), $data);

    $response->assertCreated();
    $this->assertDatabaseHas('movies', ['title' => 'New Admin Movie']);
});

test('admin can update a movie', function () {
    $movie = Movie::factory()->create(['title' => 'Old Title']);
    $data = Movie::factory()->make(['title' => 'Updated Title'])->toArray();

    $response = $this->actingAs($this->admin)->putJson(route('admin.movies.update', $movie), $data);

    $response->assertOk();
    $this->assertDatabaseHas('movies', ['title' => 'Updated Title']);
});

test('admin can delete a genre', function () {
    $genre = Genre::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.genres.destroy', $genre));

    $response->assertOk();
    $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
});
