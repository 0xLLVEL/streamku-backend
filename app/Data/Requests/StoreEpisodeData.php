<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreEpisodeData extends Data
{
    public function __construct(
        public int $episode_number,
        public string $name,
        public ?string $overview = null,
        public ?string $still_path = null,
        public ?string $air_date = null,
        public ?int $runtime = null,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'episode_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
