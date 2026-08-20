<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tvShow = App\Models\TvShow::where('slug', 'all-of-us-are-dead-99966')->first();
$season = $tvShow->seasons()->where('season_number', 1)->first();

$total = 3;
$vidkingKey = $tvShow->tmdb_id ?? $tvShow->slug;

for ($i = 1; $i <= $total; $i++) {
    $episode = $season->episodes()->firstOrCreate(
        ['episode_number' => $i],
        ['name' => "Episode $i", 'tmdb_id' => null]
    );

    $episode->videos()->firstOrCreate(
        ['site' => 'VidKing'],
        [
            'key' => (string) $vidkingKey,
            'name' => "Episode $i",
            'official' => false
        ]
    );
    echo "Created Episode $i with VidKing key $vidkingKey\n";
}

$season->update(['episode_count' => $season->episodes()->count()]);
echo "Season episode count updated to " . $season->episodes()->count() . "\n";
