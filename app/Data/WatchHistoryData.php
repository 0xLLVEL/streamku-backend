<?php

namespace App\Data;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\WatchHistory;
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
        public ?array $item = null,
    ) {}

    public static function fromModel(WatchHistory $history): self
    {
        $item = null;
        if ($history->relationLoaded('watchable') && $history->watchable) {
            $isMovie = $history->watchable_type === Movie::class;
            $item = [
                'id' => $history->watchable->id,
                'title' => $isMovie ? $history->watchable->title : $history->watchable->name,
                'poster_path' => $isMovie ? $history->watchable->poster_path : $history->watchable->still_path,
                'backdrop_path' => $isMovie ? $history->watchable->backdrop_path : null,
                'slug' => $isMovie ? $history->watchable->slug : ($history->watchable->season->tvShow->slug ?? ''),
                'season_number' => $isMovie ? null : $history->watchable->season->season_number,
                'episode_number' => $isMovie ? null : $history->watchable->episode_number,
                'tv_show_name' => $isMovie ? null : ($history->watchable->season->tvShow->name ?? ''),
            ];
        }

        return new self(
            id: $history->id,
            user_id: $history->user_id,
            media_type: match ($history->watchable_type) {
                Movie::class => 'movie',
                Episode::class => 'episode',
                default => 'unknown',
            },
            media_id: $history->watchable_id,
            progress_seconds: $history->progress_seconds,
            duration_seconds: $history->duration_seconds,
            completed: $history->completed,
            last_watched_at: $history->last_watched_at?->toIso8601String(),
            created_at: $history->created_at?->toIso8601String(),
            item: $item,
        );
    }
}
