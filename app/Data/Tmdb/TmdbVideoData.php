<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbVideoData extends Data
{
    public function __construct(
        public string $id,
        public string $key,
        public string $site,
        public string $type,
        public string $name,
        public bool $official = false,
    ) {}
}
