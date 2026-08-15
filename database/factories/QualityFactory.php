<?php

namespace Database\Factories;

use App\Models\Quality;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Quality> */
class QualityFactory extends Factory
{
    protected $model = Quality::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['360p', '480p', '720p', '1080p', '1440p', '4k']),
            'label' => fake()->randomElement(['Low', 'SD', 'HD', 'Full HD', 'QHD', 'Ultra HD']),
            'width' => 1920,
            'height' => 1080,
            'bitrate' => fake()->numberBetween(1000, 20000),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
