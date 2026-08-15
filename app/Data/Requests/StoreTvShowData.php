<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreTvShowData extends Data
{
    public function __construct(
        public string $name,
        public ?string $overview = null,
        public ?string $tagline = null,
        public ?string $poster_path = null,
        public ?string $backdrop_path = null,
        public ?string $first_air_date = null,
        public ?string $status = null,
        public ?string $type = null,
        public ?int $episode_run_time = null,
        public bool $is_featured = false,
        /** @var int[] */
        public array $genre_ids = [],
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'first_air_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'episode_run_time' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'genre_ids' => ['array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }
}
