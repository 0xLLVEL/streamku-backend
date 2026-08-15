<?php

namespace Database\Factories;

use App\Models\TvShow;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TvShow> */
class TvShowFactory extends Factory
{
    protected $model = TvShow::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.Str::random(5)),
            'overview' => fake()->paragraphs(2, true),
            'first_air_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'number_of_seasons' => fake()->numberBetween(1, 10),
            'number_of_episodes' => fake()->numberBetween(6, 200),
            'episode_run_time' => fake()->randomElement([22, 30, 45, 60]),
            'vote_average' => fake()->randomFloat(1, 1, 10),
            'vote_count' => fake()->numberBetween(0, 5000),
            'popularity' => fake()->randomFloat(3, 0, 300),
            'original_language' => 'en',
            'status' => fake()->randomElement(['Returning Series', 'Ended', 'Canceled']),
            'type' => 'Scripted',
            'is_featured' => fake()->boolean(10),
        ];
    }
}
