<?php

namespace App\Data;

use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class ReviewData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $media_type,
        public int $media_id,
        public int $rating,
        public ?string $body,
        public ?string $user_name = null,
        public ?string $created_at = null,
        public bool $is_approved = true,
    ) {}

    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            user_id: $review->user_id,
            media_type: match ($review->reviewable_type) {
                Movie::class => 'movie',
                TvShow::class => 'tv_show',
                default => 'unknown',
            },
            media_id: $review->reviewable_id,
            rating: $review->rating,
            body: $review->body,
            user_name: $review->relationLoaded('user') ? $review->user->name : null,
            created_at: $review->created_at?->toIso8601String(),
            is_approved: (bool) $review->is_approved,
        );
    }
}
