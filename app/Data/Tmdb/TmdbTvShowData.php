<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbTvShowData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $overview,
        public ?string $tagline,
        public ?string $poster_path,
        public ?string $backdrop_path,
        public ?string $first_air_date,
        public ?string $last_air_date,
        public int $number_of_seasons = 0,
        public int $number_of_episodes = 0,
        public float $vote_average = 0,
        public int $vote_count = 0,
        public float $popularity = 0,
        public ?string $original_language = null,
        public ?string $status = null,
        public ?string $type = null,
        /** @var TmdbGenreData[] */
        #[DataCollectionOf(TmdbGenreData::class)]
        public array $genres = [],
        /** @var TmdbSeasonData[] */
        #[DataCollectionOf(TmdbSeasonData::class)]
        public array $seasons = [],
    ) {}
}
