<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class WatchHistoryData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $media_type,
        public int $media_id,
        public int $progress_seconds,
        public int $duration_seconds,
        public bool $completed,
        public ?string $last_watched_at,
        public ?string $created_at = null,
    ) {}

    public static function fromModel(\App\Models\WatchHistory $history): self
    {
        return new self(
            id: $history->id,
            user_id: $history->user_id,
            media_type: match ($history->watchable_type) {
                \App\Models\Movie::class => 'movie',
                \App\Models\Episode::class => 'episode',
                default => 'unknown',
            },
            media_id: $history->watchable_id,
            progress_seconds: $history->progress_seconds,
            duration_seconds: $history->duration_seconds,
            completed: $history->completed,
            last_watched_at: $history->last_watched_at?->toIso8601String(),
            created_at: $history->created_at?->toIso8601String(),
        );
    }
}
