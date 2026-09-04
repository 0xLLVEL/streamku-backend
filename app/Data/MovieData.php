<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

/** @phpstan-consistent-constructor */
class MovieData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public string $title,
        public string $slug,
        public ?string $overview,
        public ?string $tagline,
        public ?string $poster_path,
        public ?string $backdrop_path,
        public ?string $release_date,
        public ?int $runtime,
        public float $vote_average,
        public int $vote_count,
        public float $popularity,
        public ?string $original_language,
        public ?string $status,
        public bool $is_featured,
        /** @var GenreData[]|Lazy */
        #[DataCollectionOf(GenreData::class)]
        public Lazy|array $genres,
        /** @var CastData[]|Lazy */
        #[DataCollectionOf(CastData::class)]
        public Lazy|array $cast,
        /** @var VideoData[]|Lazy */
        #[DataCollectionOf(VideoData::class)]
        public Lazy|array $videos,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?WatchHistoryData $history = null,
        public ?array $images = null,
    ) {}
}
