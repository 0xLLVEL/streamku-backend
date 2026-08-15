<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class WatchlistData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $watchlistable_type,
        public int $watchlistable_id,
        public ?MovieData $movie = null,
        public ?TvShowData $tv_show = null,
        public ?string $created_at = null,
    ) {}
}
