<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tvShow = App\Models\TvShow::first();
$tvShow->load(['genres', 'cast', 'videos', 'seasons.episodes.videos', 'seasons.episodes.season']);

// Simulate authenticated user load
$tvShow->loadMissing([
    'seasons.episodes.watchHistories' => fn($q) => $q->where('user_id', 1)
]);

$data = App\Data\TvShowData::from($tvShow)->toArray();

echo json_encode([
    'season_number' => $data['seasons'][0]['episodes'][0]['season_number'] ?? 'MISSING',
    'videos' => $data['seasons'][0]['episodes'][0]['videos'] ?? 'MISSING',
    'history' => $data['seasons'][0]['episodes'][0]['history'] ?? 'MISSING'
]);
