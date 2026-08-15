<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class QualityData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $label,
        public int $width,
        public int $height,
        public ?int $bitrate,
        public int $sort_order,
    ) {}
}
