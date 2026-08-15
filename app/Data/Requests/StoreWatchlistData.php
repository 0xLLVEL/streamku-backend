<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreWatchlistData extends Data
{
    public function __construct(
        public int $watchlistable_id,
        public string $watchlistable_type,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'watchlistable_id' => ['required', 'integer'],
            'watchlistable_type' => ['required', 'string', 'in:movie,tv_show'],
        ];
    }
}
