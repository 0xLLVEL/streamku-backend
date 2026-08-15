<?php

use App\Models\Media;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('media stream supports byte ranges', function () {
    Storage::fake('local');
    Storage::disk('local')->put('test.mp4', str_repeat('A', 1000));

    $media = Media::factory()->create([
        'disk' => 'local',
        'path' => 'test.mp4',
        'mime_type' => 'video/mp4',
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('media.stream', $media), [
        'Range' => 'bytes=0-99',
    ]);

    $response->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 0-99/1000')
        ->assertHeader('Content-Length', '100');
});

test('public users can list movie media', function () {
    $movie = Movie::factory()->create();
    Media::factory()->count(3)->create([
        'mediable_id' => $movie->id,
        'mediable_type' => Movie::class,
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('movies.media', $movie));

    $response->assertOk()
        ->assertJsonCount(3, 'data.default');
});
