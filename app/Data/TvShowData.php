<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

/** @phpstan-consistent-constructor */
class TvShowData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public string $name,
        public string $slug,
        public ?string $overview,
        public ?string $tagline,
        public ?string $poster_path,
        public ?string $backdrop_path,
        public ?string $first_air_date,
        public ?string $last_air_date,
        public int $number_of_seasons,
        public int $number_of_episodes,
        public ?int $episode_run_time,
        public float $vote_average,
        public int $vote_count,
        public float $popularity,
        public ?string $original_language,
        public ?string $status,
        public ?string $type,
        public bool $is_featured,
        /** @var GenreData[]|Lazy */
        #[DataCollectionOf(GenreData::class)]
        public Lazy|array $genres,
        /** @var SeasonData[]|Lazy */
        #[DataCollectionOf(SeasonData::class)]
        public Lazy|array $seasons,
        /** @var CastData[]|Lazy */
        #[DataCollectionOf(CastData::class)]
        public Lazy|array $cast,
        /** @var VideoData[]|Lazy */
        #[DataCollectionOf(VideoData::class)]
        public Lazy|array $videos,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
