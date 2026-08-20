<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ep = App\Models\Episode::with('videos')->where('episode_number', 2)->first();
echo json_encode($ep->videos->toArray());
