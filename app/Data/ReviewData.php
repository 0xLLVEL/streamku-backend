<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class ReviewData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $reviewable_type,
        public int $reviewable_id,
        public int $rating,
        public ?string $body,
        public ?string $user_name = null,
        public ?string $created_at = null,
    ) {}
}
