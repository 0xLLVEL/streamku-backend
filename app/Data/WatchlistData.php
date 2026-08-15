<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class WatchlistData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $media_type,
        public int $media_id,
        public ?array $media_details = null,
        public ?string $created_at = null,
    ) {}

    public static function fromModel(\App\Models\Watchlist $watchlist): self
    {
        $mediaDetails = null;
        if ($watchlist->relationLoaded('watchlistable') && $watchlist->watchlistable) {
            $mediaDetails = [
                'id' => $watchlist->watchlistable->id,
                'title' => $watchlist->watchlistable->title ?? $watchlist->watchlistable->name,
                'poster_path' => $watchlist->watchlistable->poster_path,
                'slug' => $watchlist->watchlistable->slug,
            ];
        }

        return new self(
            id: $watchlist->id,
            user_id: $watchlist->user_id,
            media_type: match ($watchlist->watchlistable_type) {
                \App\Models\Movie::class => 'movie',
                \App\Models\TvShow::class => 'tv_show',
                default => 'unknown',
            },
            media_id: $watchlist->watchlistable_id,
            media_details: $mediaDetails,
            created_at: $watchlist->created_at?->toIso8601String(),
        );
    }
}
