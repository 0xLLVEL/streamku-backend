<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class CastData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public string $name,
        public ?string $character,
        public ?string $profile_path,
        public int $order,
    ) {}
}
