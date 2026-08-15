<?php

namespace App\Data\Tmdb;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class TmdbPaginatedData extends Data
{
    public function __construct(
        public int $page,
        public int $total_pages,
        public int $total_results,
        /** @var array<int, mixed> */
        public array $results = [],
    ) {}
}
