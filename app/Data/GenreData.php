<?php

namespace App\Data;

use App\Models\Genre;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class GenreData extends Data
{
    public function __construct(
        public int $id,
        public ?int $tmdb_id,
        public string $name,
        public string $slug,
    ) {}

    public static function fromModel(Genre $genre): self
    {
        return new self(
            id: $genre->id,
            tmdb_id: $genre->tmdb_id,
            name: $genre->name,
            slug: $genre->slug,
        );
    }
}
