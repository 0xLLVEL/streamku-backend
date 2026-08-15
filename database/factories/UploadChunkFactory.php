<?php

namespace Database\Factories;

use App\Models\Upload;
use App\Models\UploadChunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UploadChunk> */
class UploadChunkFactory extends Factory
{
    protected $model = UploadChunk::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'upload_id' => Upload::factory(),
            'chunk_number' => fake()->numberBetween(0, 100),
            'size' => 5 * 1024 * 1024,
            'path' => 'chunks/'.fake()->uuid().'/chunk_0',
            'checksum' => md5(fake()->sentence()),
        ];
    }
}
