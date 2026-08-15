<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbMovieData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $overview,
        public ?string $tagline,
        public ?string $poster_path,
        public ?string $backdrop_path,
        public ?string $release_date,
        public ?int $runtime,
        public float $vote_average = 0,
        public int $vote_count = 0,
        public float $popularity = 0,
        public ?string $original_language = null,
        public ?string $status = null,
        /** @var TmdbGenreData[] */
        #[DataCollectionOf(TmdbGenreData::class)]
        public array $genres = [],
    ) {}
}
