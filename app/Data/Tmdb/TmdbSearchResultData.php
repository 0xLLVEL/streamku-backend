<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbSearchResultData extends Data
{
    public function __construct(
        public int $id,
        public string $media_type,
        public ?string $title = null,
        public ?string $name = null,
        public ?string $overview = null,
        public ?string $poster_path = null,
        public ?string $release_date = null,
        public ?string $first_air_date = null,
        public float $vote_average = 0,
        public float $popularity = 0,
    ) {}

    public function displayTitle(): string
    {
        return $this->title ?? $this->name ?? '';
    }
}
