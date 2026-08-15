<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UpdateReviewData extends Data
{
    public function __construct(
        public ?int $rating = null,
        public ?string $body = null,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'body' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
