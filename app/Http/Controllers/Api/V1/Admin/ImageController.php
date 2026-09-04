<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'type' => ['sometimes', 'string', 'in:poster,backdrop'],
        ]);

        $file = $request->file('image');
        $type = $validated['type'] ?? 'poster';
        $folder = $type === 'backdrop' ? 'media/images/backdrops' : 'media/images/posters';
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        return $this->success([
            'path' => '/storage/'.$path,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
