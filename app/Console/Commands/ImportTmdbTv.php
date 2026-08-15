<?php

namespace App\Console\Commands;

use App\Services\TmdbImportService;
use Illuminate\Console\Command;

class ImportTmdbTv extends Command
{
    /** @var string */
    protected $signature = 'tmdb:import-tv {tmdbId : The TMDB TV show ID}';

    /** @var string */
    protected $description = 'Import a single TV show (with seasons and episodes) from TMDB by ID';

    public function handle(TmdbImportService $service): int
    {
        $tmdbId = (int) $this->argument('tmdbId');

        $this->info("Importing TV show with TMDB ID: {$tmdbId}...");

        $tvShow = $service->importTvShow($tmdbId);

        $this->info("Imported: {$tvShow->name} ({$tvShow->number_of_seasons} seasons, {$tvShow->number_of_episodes} episodes)");

        return self::SUCCESS;
    }
}
