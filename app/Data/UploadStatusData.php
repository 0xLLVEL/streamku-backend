<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UploadStatusData extends Data
{
    public function __construct(
        public string $upload_id,
        public string $status,
        public int $received_chunks,
        public int $total_chunks,
        public float $progress_percent,
        /** @var int[] */
        public array $missing_chunks,
    ) {}
}
