<?php

namespace App\Console\Commands;

use App\Contracts\TmdbPort;
use App\Services\TmdbImportService;
use Illuminate\Console\Command;

class ImportTmdbPopular extends Command
{
    /** @var string */
    protected $signature = 'tmdb:import-popular
        {--type=movie : The type to import (movie or tv)}
        {--pages=1 : Number of pages to import (20 items per page)}';

    /** @var string */
    protected $description = 'Import popular movies or TV shows from TMDB for seeding';

    public function handle(TmdbPort $client, TmdbImportService $service): int
    {
        $type = $this->option('type');
        $pages = (int) $this->option('pages');

        $this->info("Importing {$pages} page(s) of popular {$type}s from TMDB...");

        $imported = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $data = $type === 'movie'
                ? $client->getPopularMovies($page)
                : $client->getPopularTv($page);

            foreach ($data['results'] ?? [] as $item) {
                try {
                    if ($type === 'movie') {
                        $service->importMovie($item['id']);
                    } else {
                        $service->importTvShow($item['id']);
                    }
                    $imported++;
                    $this->line('  Imported: '.($item['title'] ?? $item['name']));
                } catch (\Throwable $e) {
                    $this->warn('  Failed: '.($item['title'] ?? $item['name'])." - {$e->getMessage()}");
                }
            }
        }

        $this->info("Done. Imported {$imported} {$type}(s).");

        return self::SUCCESS;
    }
}
