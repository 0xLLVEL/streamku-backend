<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

/** @phpstan-consistent-constructor */
class SeasonData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public int $tv_show_id,
        public int $season_number,
        public string $name,
        public ?string $overview,
        public ?string $poster_path,
        public ?string $air_date,
        public int $episode_count,
        /** @var EpisodeData[]|Lazy */
        #[DataCollectionOf(EpisodeData::class)]
        public Lazy|array $episodes,
    ) {}
}
