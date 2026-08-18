<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UpdateMovieData extends Data
{
    public function __construct(
        public ?string $title = null,
        public ?string $overview = null,
        public ?string $tagline = null,
        public ?string $poster_path = null,
        public ?string $backdrop_path = null,
        public ?string $release_date = null,
        public ?int $runtime = null,
        public ?float $vote_average = null,
        public ?string $original_language = null,
        public ?string $status = null,
        public ?string $trailer_url = null,
        public ?bool $is_featured = null,
        /** @var int[]|null */
        public ?array $genre_ids = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'release_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'original_language' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }
}
