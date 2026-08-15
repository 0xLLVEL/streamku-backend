<?php

namespace App\Console\Commands;

use App\Services\ChunkedUploadService;
use Illuminate\Console\Command;

class CleanupUploads extends Command
{
    /** @var string */
    protected $signature = 'uploads:cleanup';

    /** @var string */
    protected $description = 'Clean up expired and stale upload sessions';

    public function handle(ChunkedUploadService $service): int
    {
        $this->info('Cleaning up expired uploads...');

        $cleaned = $service->cleanupExpired();

        $this->info("Cleaned up {$cleaned} expired upload(s).");

        return self::SUCCESS;
    }
}
