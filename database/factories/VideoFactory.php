<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Video> */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'videoable_id' => Movie::factory(),
            'videoable_type' => Movie::class,
            'key' => fake()->regexify('[A-Za-z0-9]{11}'),
            'site' => 'YouTube',
            'type' => fake()->randomElement(['Trailer', 'Teaser', 'Clip', 'Featurette']),
            'name' => fake()->sentence(3),
            'official' => fake()->boolean(80),
        ];
    }
}
