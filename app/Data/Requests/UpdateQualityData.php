<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UpdateQualityData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $bitrate = null,
        public ?int $sort_order = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:50'],
            'label' => ['sometimes', 'string', 'max:100'],
            'width' => ['sometimes', 'integer', 'min:1'],
            'height' => ['sometimes', 'integer', 'min:1'],
            'bitrate' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
