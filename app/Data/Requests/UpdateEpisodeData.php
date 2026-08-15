<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UpdateEpisodeData extends Data
{
    public function __construct(
        public ?int $episode_number = null,
        public ?string $name = null,
        public ?string $overview = null,
        public ?string $still_path = null,
        public ?string $air_date = null,
        public ?int $runtime = null,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'episode_number' => ['sometimes', 'integer', 'min:1'],
            'name' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
