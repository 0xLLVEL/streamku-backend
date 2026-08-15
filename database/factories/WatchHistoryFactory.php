<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WatchHistory> */
class WatchHistoryFactory extends Factory
{
    protected $model = WatchHistory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $duration = fake()->numberBetween(3600, 10800);

        return [
            'user_id' => User::factory(),
            'watchable_id' => Movie::factory(),
            'watchable_type' => Movie::class,
            'progress_seconds' => fake()->numberBetween(0, $duration),
            'duration_seconds' => $duration,
            'completed' => fake()->boolean(30),
            'last_watched_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
