<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reviewable_id' => Movie::factory(),
            'reviewable_type' => Movie::class,
            'rating' => fake()->numberBetween(1, 10),
            'body' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
