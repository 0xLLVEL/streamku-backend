<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreQualityData extends Data
{
    public function __construct(
        public string $name,
        public string $label,
        public int $width,
        public int $height,
        public ?int $bitrate = null,
        public int $sort_order = 0,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:qualities,name'],
            'label' => ['required', 'string', 'max:100'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
            'bitrate' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
