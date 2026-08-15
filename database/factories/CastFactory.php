<?php

namespace Database\Factories;

use App\Models\Cast;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Cast> */
class CastFactory extends Factory
{
    protected $model = Cast::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'castable_id' => Movie::factory(),
            'castable_type' => Movie::class,
            'name' => fake()->name(),
            'character' => fake()->name(),
            'order' => fake()->numberBetween(0, 20),
        ];
    }
}
