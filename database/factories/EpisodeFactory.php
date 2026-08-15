<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Episode> */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'episode_number' => fake()->numberBetween(1, 24),
            'name' => fake()->sentence(4),
            'overview' => fake()->paragraph(),
            'air_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'runtime' => fake()->randomElement([22, 30, 45, 55, 60]),
            'vote_average' => fake()->randomFloat(1, 1, 10),
            'vote_count' => fake()->numberBetween(0, 500),
        ];
    }
}
