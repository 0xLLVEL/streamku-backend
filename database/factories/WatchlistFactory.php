<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Watchlist> */
class WatchlistFactory extends Factory
{
    protected $model = Watchlist::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'watchlistable_id' => Movie::factory(),
            'watchlistable_type' => Movie::class,
        ];
    }
}
