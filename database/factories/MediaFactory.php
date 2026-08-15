<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'mediable_id' => Movie::factory(),
            'mediable_type' => Movie::class,
            'type' => 'video',
            'collection' => 'default',
            'disk' => 'local',
            'path' => 'media/videos/'.fake()->uuid().'.mp4',
            'original_filename' => fake()->word().'.mp4',
            'mime_type' => 'video/mp4',
            'size' => fake()->numberBetween(1000000, 5000000000),
            'duration' => fake()->numberBetween(60, 7200),
            'is_primary' => false,
        ];
    }

    public function image(): static
    {
        return $this->state([
            'type' => 'image',
            'path' => 'media/images/'.fake()->uuid().'.jpg',
            'original_filename' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10000, 5000000),
            'duration' => null,
        ]);
    }
}
