<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreSeasonData extends Data
{
    public function __construct(
        public int $season_number,
        public string $name,
        public ?string $overview = null,
        public ?string $poster_path = null,
        public ?string $air_date = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'season_number' => ['required', 'integer', 'min:0'],
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
        ];
    }
}
