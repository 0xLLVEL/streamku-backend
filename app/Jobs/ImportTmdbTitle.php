<?php

namespace App\Jobs;

use App\Services\TmdbImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ImportTmdbTitle implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $tmdbId,
        public string $type,
        public ?string $language = null,
    ) {}

    public function handle(TmdbImportService $importService): void
    {
        if ($this->type === 'movie') {
            $importService->importMovie($this->tmdbId, $this->language);
        } else {
            $importService->importTvShow($this->tmdbId, $this->language);
        }

        Cache::forget('browse_rows');
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}
