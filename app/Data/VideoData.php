<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class VideoData extends Data
{
    public function __construct(
        public int $id,
        public ?string $tmdb_id,
        public string $key,
        public string $site,
        public string $name,
        public bool $official,
    ) {}
}
