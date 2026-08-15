<?php

namespace Database\Factories;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Upload> */
class UploadFactory extends Factory
{
    protected $model = Upload::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $totalSize = fake()->numberBetween(1000000, 500000000);
        $chunkSize = 5 * 1024 * 1024;

        return [
            'upload_id' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'filename' => fake()->word().'.mp4',
            'mime_type' => 'video/mp4',
            'total_size' => $totalSize,
            'chunk_size' => $chunkSize,
            'total_chunks' => (int) ceil($totalSize / $chunkSize),
            'received_chunks' => 0,
            'status' => 'pending',
            'disk' => 'local',
            'type' => 'video',
            'collection' => 'default',
            'expires_at' => now()->addHours(24),
        ];
    }
}
