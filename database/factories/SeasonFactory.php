<?php

namespace Database\Factories;

use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Season> */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tv_show_id' => TvShow::factory(),
            'season_number' => fake()->numberBetween(1, 10),
            'name' => 'Season '.fake()->numberBetween(1, 10),
            'overview' => fake()->paragraph(),
            'air_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'episode_count' => fake()->numberBetween(6, 24),
        ];
    }
}
