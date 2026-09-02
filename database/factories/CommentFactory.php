<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Comment> */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commentable_id' => Movie::factory(),
            'commentable_type' => Movie::class,
            'parent_id' => null,
            'body' => fake()->sentence(),
            'is_approved' => true,
        ];
    }
}
