<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreCommentData extends Data
{
    public function __construct(
        public int $media_id,
        public string $media_type,
        public string $body,
        public ?int $parent_id = null,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'media_id' => ['required', 'integer'],
            'media_type' => ['required', 'string', 'in:movie,tv_show'],
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }
}
