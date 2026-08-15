<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Movie> */
class MovieFactory extends Factory
{
    protected $model = Movie::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title.'-'.Str::random(5)),
            'overview' => fake()->paragraphs(2, true),
            'tagline' => fake()->sentence(),
            'release_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'runtime' => fake()->numberBetween(80, 180),
            'vote_average' => fake()->randomFloat(1, 1, 10),
            'vote_count' => fake()->numberBetween(0, 10000),
            'popularity' => fake()->randomFloat(3, 0, 500),
            'original_language' => 'en',
            'status' => 'Released',
            'is_featured' => fake()->boolean(10),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
