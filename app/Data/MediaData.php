<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class MediaData extends Data
{
    public function __construct(
        public int $id,
        public string $type,
        public string $collection,
        public ?QualityData $quality,
        public ?string $url,
        public string $original_filename,
        public string $mime_type,
        public int $size,
        public ?int $duration,
        public ?int $width,
        public ?int $height,
        public bool $is_primary,
        public ?string $created_at = null,
    ) {}
}
