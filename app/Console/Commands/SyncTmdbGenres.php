<?php

namespace App\Console\Commands;

use App\Services\TmdbImportService;
use Illuminate\Console\Command;

class SyncTmdbGenres extends Command
{
    /** @var string */
    protected $signature = 'tmdb:sync-genres';

    /** @var string */
    protected $description = 'Sync all movie and TV genres from TMDB';

    public function handle(TmdbImportService $service): int
    {
        $this->info('Syncing genres from TMDB...');

        $service->syncAllGenres();

        $this->info('Genres synced successfully.');

        return self::SUCCESS;
    }
}
