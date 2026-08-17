<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$media = App\Models\Media::where('type', 'video')->first();
if (!$media) {
    echo "No video media found\n";
    exit;
}
$movie = $media->mediable;

$controller = app(App\Http\Controllers\Api\V1\MediaStreamController::class);
$request = Illuminate\Http\Request::create("/api/v1/movies/{$movie->slug}/media", 'GET');
$response = $controller->movieMedia($movie);

echo "Movie Slug: " . $movie->slug . "\n";
echo "Response: " . $response->getContent() . "\n";
