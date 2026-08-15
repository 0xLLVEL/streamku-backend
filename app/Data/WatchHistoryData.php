<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class WatchHistoryData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $watchable_type,
        public int $watchable_id,
        public int $progress_seconds,
        public int $duration_seconds,
        public bool $completed,
        public ?string $last_watched_at,
        public ?string $created_at = null,
    ) {}
}
