<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbCastData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $character,
        public ?string $profile_path,
        public int $order = 0,
    ) {}
}
