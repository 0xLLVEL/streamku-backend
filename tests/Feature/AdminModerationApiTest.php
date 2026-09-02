<?php

use App\Models\Comment;
use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create(['is_admin' => false]);
});

test('non-admins cannot access admin review routes', function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.reviews.index'));

    $response->assertForbidden();
});

test('admin can list reviews with meta', function () {
    Review::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson(route('admin.reviews.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['user_name', 'media_title', 'is_approved']], 'meta' => ['total']]);
});

test('admin can filter hidden reviews', function () {
    Review::factory()->create(['is_approved' => true]);
    Review::factory()->create(['is_approved' => false]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.reviews.index', ['is_approved' => 0]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_approved', false);
});

test('admin can hide and approve a review', function () {
    $movie = Movie::factory()->create();
    $review = Review::factory()->create([
        'reviewable_id' => $movie->id,
        'reviewable_type' => Movie::class,
    ]);

    $this->actingAs($this->admin)->postJson(route('admin.reviews.hide', $review))->assertOk()->assertJsonPath('data.is_approved', false);
    $this->assertDatabaseHas('reviews', ['id' => $review->id, 'is_approved' => false]);

    $this->actingAs($this->admin)->postJson(route('admin.reviews.approve', $review))->assertOk()->assertJsonPath('data.is_approved', true);
    $this->assertDatabaseHas('reviews', ['id' => $review->id, 'is_approved' => true]);
});

test('admin can delete a review', function () {
    $review = Review::factory()->create();

    $this->actingAs($this->admin)->deleteJson(route('admin.reviews.destroy', $review))->assertOk();
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('non-admins cannot access admin comment routes', function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.comments.index'));

    $response->assertForbidden();
});

test('admin can list comments with meta', function () {
    Comment::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)->getJson(route('admin.comments.index'));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['user_name', 'media_title', 'is_approved']], 'meta' => ['total']]);
});

test('admin can hide approve and delete a comment', function () {
    $comment = Comment::factory()->create();

    $this->actingAs($this->admin)->postJson(route('admin.comments.hide', $comment))->assertOk()->assertJsonPath('data.is_approved', false);
    $this->actingAs($this->admin)->postJson(route('admin.comments.approve', $comment))->assertOk();
    $this->actingAs($this->admin)->deleteJson(route('admin.comments.destroy', $comment))->assertOk();
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});
