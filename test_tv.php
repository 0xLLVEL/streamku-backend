<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tvShow = App\Models\TvShow::with(['genres', 'cast', 'videos', 'seasons.episodes.videos', 'seasons.episodes.season'])->first();
$data = App\Data\TvShowData::from($tvShow)->toArray();

echo json_encode([
    'season_number' => $data['seasons'][0]['episodes'][0]['season_number'] ?? 'MISSING',
    'videos' => $data['seasons'][0]['episodes'][0]['videos'] ?? 'MISSING'
]);
