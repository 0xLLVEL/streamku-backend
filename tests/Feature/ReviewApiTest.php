<?php

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can store a review', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson(route('reviews.store'), [
        'reviewable_id' => $movie->id,
        'reviewable_type' => 'movie',
        'rating' => 8,
        'content' => 'Great movie!',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'rating' => 8,
    ]);
});

test('user can update their review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id, 'rating' => 5]);

    $response = $this->actingAs($user)->putJson(route('reviews.update', $review), [
        'rating' => 9,
        'content' => 'Changed my mind.',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'rating' => 9,
    ]);
});

test('user cannot update someone elses review', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson(route('reviews.update', $review), [
        'rating' => 9,
    ]);

    $response->assertForbidden();
});

test('can get reviews for a specific title', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    Review::factory()->count(2)->create([
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
    ]);

    $response = $this->actingAs($user)->getJson(route('reviews.for-title', ['type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
