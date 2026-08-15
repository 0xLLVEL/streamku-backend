<?php

namespace App\Console\Commands;

use App\Services\TmdbImportService;
use Illuminate\Console\Command;

class ImportTmdbMovie extends Command
{
    /** @var string */
    protected $signature = 'tmdb:import-movie {tmdbId : The TMDB movie ID}';

    /** @var string */
    protected $description = 'Import a single movie from TMDB by ID';

    public function handle(TmdbImportService $service): int
    {
        $tmdbId = (int) $this->argument('tmdbId');

        $this->info("Importing movie with TMDB ID: {$tmdbId}...");

        $movie = $service->importMovie($tmdbId);

        $this->info("Imported: {$movie->title} ({$movie->release_date?->year})");

        return self::SUCCESS;
    }
}
