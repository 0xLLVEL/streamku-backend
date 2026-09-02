<?php

use App\Models\Movie;
use App\Models\Quality;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('admin can initiate upload session', function () {
    $movie = Movie::factory()->create();
    $quality = Quality::factory()->create();

    $response = $this->actingAs($this->admin)->postJson(route('admin.uploads.initiate'), [
        'filename' => 'test.mp4',
        'mime_type' => 'video/mp4',
        'total_size' => 10485760, // 10MB
        'media_id' => $movie->id,
        'media_type' => 'movie',
        'type' => 'video',
        'quality_id' => $quality->id,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['upload_id', 'chunk_size', 'total_chunks']]);

    $this->assertDatabaseHas('uploads', [
        'filename' => 'test.mp4',
        'mediable_id' => $movie->id,
        'mediable_type' => Movie::class,
        'status' => 'pending',
    ]);
});

test('admin can upload chunk', function () {
    Storage::fake('local');
    $upload = Upload::factory()->create(['user_id' => $this->admin->id]);
    $file = UploadedFile::fake()->create('chunk.tmp', 1024); // 1MB chunk

    $response = $this->actingAs($this->admin)->postJson(route('admin.uploads.chunk', $upload->upload_id), [
        'chunk_number' => 0,
        'chunk' => $file,
    ]);

    $response->assertOk()
        ->assertJsonPath('chunk_number', 0);

    $this->assertDatabaseHas('upload_chunks', [
        'upload_id' => $upload->id,
        'chunk_number' => 0,
    ]);

    Storage::disk('local')->assertExists("chunks/{$upload->upload_id}/chunk_0");
});

test('admin can check upload status', function () {
    $upload = Upload::factory()->create([
        'user_id' => $this->admin->id,
        'total_chunks' => 3,
        'received_chunks' => 1,
    ]);

    $upload->chunks()->create([
        'chunk_number' => 0,
        'size' => 1024,
        'path' => 'path/0',
        'checksum' => 'md5',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.uploads.status', $upload->upload_id));

    $response->assertOk()
        ->assertJsonPath('data.missing_chunks', [1, 2]);
});

test('admin can cancel upload session', function () {
    Storage::fake('local');
    $upload = Upload::factory()->create(['user_id' => $this->admin->id]);
    Storage::disk('local')->put("chunks/{$upload->upload_id}/chunk_0", 'data');

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.uploads.cancel', $upload->upload_id));

    $response->assertOk();
    $this->assertDatabaseHas('uploads', ['id' => $upload->id, 'status' => 'cancelled']);
    Storage::disk('local')->assertMissing("chunks/{$upload->upload_id}");
});
