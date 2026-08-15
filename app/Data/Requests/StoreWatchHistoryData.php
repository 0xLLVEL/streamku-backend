<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreWatchHistoryData extends Data
{
    public function __construct(
        public int $watchable_id,
        public string $watchable_type,
        public int $progress_seconds = 0,
        public int $duration_seconds = 0,
        public bool $completed = false,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'watchable_id' => ['required', 'integer'],
            'watchable_type' => ['required', 'string', 'in:movie,episode'],
            'progress_seconds' => ['integer', 'min:0'],
            'duration_seconds' => ['integer', 'min:0'],
            'completed' => ['boolean'],
        ];
    }
}
