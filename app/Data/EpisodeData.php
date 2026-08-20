<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

/** @phpstan-consistent-constructor */
class EpisodeData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public int $season_id,
        public ?int $season_number,
        public int $episode_number,
        public string $name,
        public ?string $overview,
        public ?string $still_path,
        public ?string $air_date,
        public ?int $runtime,
        public float $vote_average,
        public int $vote_count,
        /** @var VideoData[]|Lazy */
        #[DataCollectionOf(VideoData::class)]
        public Lazy|array $videos,
        public ?WatchHistoryData $history = null,
    ) {}

    public static function fromModel(\App\Models\Episode $episode): self
    {
        return new self(
            id: $episode->id,
            tmdb_id: $episode->tmdb_id,
            season_id: $episode->season_id,
            season_number: $episode->relationLoaded('season') ? $episode->season->season_number : null,
            episode_number: $episode->episode_number,
            name: $episode->name,
            overview: $episode->overview,
            still_path: $episode->still_path,
            air_date: $episode->air_date?->format('Y-m-d'),
            runtime: $episode->runtime,
            vote_average: $episode->vote_average,
            vote_count: $episode->vote_count,
            videos: Lazy::whenLoaded('videos', $episode, fn () => VideoData::collect($episode->videos)),
            history: $episode->relationLoaded('watchHistories') && $episode->watchHistories->first() ? WatchHistoryData::fromModel($episode->watchHistories->first()) : null,
        );
    }
}


