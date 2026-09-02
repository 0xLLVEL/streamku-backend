<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreReviewData extends Data
{
    public function __construct(
        public int $media_id,
        public string $media_type,
        public int $rating,
        public ?string $body = null,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'media_id' => ['required', 'integer'],
            'media_type' => ['required', 'string', 'in:movie,tv_show'],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'body' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
