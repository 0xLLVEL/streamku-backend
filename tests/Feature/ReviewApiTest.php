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
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'rating' => 8,
        'content' => 'Great movie!',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'rating' => 8,
    ]);
});

test('store response includes the user name', function () {
    $user = User::factory()->create(['name' => 'Alice Test']);
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson(route('reviews.store'), [
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'rating' => 8,
        'body' => 'Great movie!',
    ]);

    $response->assertCreated()->assertJsonPath('data.user_name', 'Alice Test');
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

test('can get reviews for a specific title with aggregate', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    Review::factory()->count(2)->create([
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
        'rating' => 8,
    ]);
    Review::factory()->create([
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
        'rating' => 2,
        'is_approved' => false,
    ]);

    $response = $this->actingAs($user)->getJson(route('reviews.for-title', ['media_type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonPath('data.avg_rating', 8)
        ->assertJsonPath('data.review_count', 2)
        ->assertJsonCount(2, 'data.reviews');
});

test('title reviews include the requesting users review', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    Review::factory()->create([
        'user_id' => $user->id,
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
        'rating' => 7,
        'body' => 'Mine',
    ]);

    $response = $this->actingAs($user)->getJson(route('reviews.for-title', ['media_type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonPath('data.my_review.rating', 7)
        ->assertJsonPath('data.my_review.user_id', $user->id);
});

test('guests can read title reviews without a my_review', function () {
    $movie = Movie::factory()->create();
    Review::factory()->create([
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
        'rating' => 5,
    ]);

    $response = $this->getJson(route('reviews.for-title', ['media_type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonPath('data.my_review', null)
        ->assertJsonPath('data.review_count', 1);
});
