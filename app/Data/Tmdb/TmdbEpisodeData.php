<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbEpisodeData extends Data
{
    public function __construct(
        public int $id,
        public int $episode_number,
        public string $name,
        public ?string $overview,
        public ?string $still_path,
        public ?string $air_date,
        public ?int $runtime,
        public float $vote_average = 0,
        public int $vote_count = 0,
    ) {}
}
