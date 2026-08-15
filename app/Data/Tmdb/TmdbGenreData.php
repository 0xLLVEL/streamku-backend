<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbGenreData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
