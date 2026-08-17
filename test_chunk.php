<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$upload = App\Models\Upload::latest()->first();
if (!$upload) {
    echo "No upload found\n";
    exit;
}

echo "Testing upload chunk for upload_id: " . $upload->upload_id . "\n";

$request = Illuminate\Http\Request::create(
    "/api/v1/admin/uploads/{$upload->upload_id}/chunk",
    'POST',
    ['chunk_number' => 0]
);
// Mock the chunk file
$file = Illuminate\Http\UploadedFile::fake()->create('chunk.blob', 1024, 'application/octet-stream');
$request->files->set('chunk', $file);

// Find an admin user to mock authentication
$user = App\Models\User::where('is_admin', true)->first() ?: App\Models\User::first();
$request->setUserResolver(fn() => $user);

$controller = app(App\Http\Controllers\Api\V1\Admin\UploadController::class);

try {
    $response = $controller->chunk($request, $upload);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
