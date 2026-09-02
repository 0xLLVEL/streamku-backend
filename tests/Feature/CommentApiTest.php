<?php

use App\Models\Comment;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can store a comment on a title', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();

    $response = $this->actingAs($user)->postJson(route('comments.store'), [
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'body' => 'Loved it!',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('comments', [
        'user_id' => $user->id,
        'body' => 'Loved it!',
    ]);
});

test('user cannot comment on an invalid media type', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('comments.store'), [
        'media_id' => 1,
        'media_type' => 'banana',
        'body' => 'Hi',
    ]);

    $response->assertUnprocessable();
});

test('user can reply to a comment', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    $parent = Comment::factory()->create([
        'commentable_id' => $movie->id,
        'commentable_type' => Movie::class,
    ]);

    $response = $this->actingAs($user)->postJson(route('comments.store'), [
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'body' => 'A reply',
        'parent_id' => $parent->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.parent_id', $parent->id);
});

test('user can update their own comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id, 'body' => 'Original']);

    $response = $this->actingAs($user)->putJson(route('comments.update', $comment), [
        'body' => 'Edited',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'body' => 'Edited']);
});

test('user cannot update someone elses comment', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->putJson(route('comments.update', $comment), [
        'body' => 'Hijack',
    ]);

    $response->assertForbidden();
});

test('user can delete their own comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson(route('comments.destroy', $comment));

    $response->assertOk();
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('title comments return only approved top-level threads', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    $visible = Comment::factory()->create([
        'user_id' => $user->id,
        'commentable_id' => $movie->id,
        'commentable_type' => Movie::class,
        'body' => 'Visible',
    ]);
    Comment::factory()->create([
        'commentable_id' => $movie->id,
        'commentable_type' => Movie::class,
        'body' => 'Hidden',
        'is_approved' => false,
    ]);
    Comment::factory()->create([
        'user_id' => $user->id,
        'commentable_id' => $movie->id,
        'commentable_type' => Movie::class,
        'parent_id' => $visible->id,
        'body' => 'Nested reply',
    ]);

    $response = $this->actingAs($user)->getJson(route('comments.for-title', ['media_type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonCount(1, 'data.comments')
        ->assertJsonCount(1, 'data.comments.0.replies')
        ->assertJsonPath('data.total', 2);
});

test('guests can read title comments', function () {
    $movie = Movie::factory()->create();
    Comment::factory()->create([
        'commentable_id' => $movie->id,
        'commentable_type' => Movie::class,
        'body' => 'Public comment',
    ]);

    $response = $this->getJson(route('comments.for-title', ['media_type' => 'movie', 'id' => $movie->id]));

    $response->assertOk()
        ->assertJsonCount(1, 'data.comments')
        ->assertJsonPath('data.total', 1);
});
