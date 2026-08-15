<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class EpisodeData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public int $season_id,
        public int $episode_number,
        public string $name,
        public ?string $overview,
        public ?string $still_path,
        public ?string $air_date,
        public ?int $runtime,
        public float $vote_average,
        public int $vote_count,
    ) {}
}
