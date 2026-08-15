<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbSeasonData extends Data
{
    public function __construct(
        public int $id,
        public int $season_number,
        public string $name,
        public ?string $overview,
        public ?string $poster_path,
        public ?string $air_date,
        public int $episode_count = 0,
    ) {}
}
