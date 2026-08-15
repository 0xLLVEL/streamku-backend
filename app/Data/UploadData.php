<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UploadData extends Data
{
    public function __construct(
        public string $upload_id,
        public string $filename,
        public int $total_size,
        public int $chunk_size,
        public int $total_chunks,
        public int $received_chunks,
        public string $status,
        public float $progress_percent,
    ) {}
}
