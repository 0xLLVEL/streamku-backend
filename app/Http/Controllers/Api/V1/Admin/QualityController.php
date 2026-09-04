<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\QualityData;
use App\Http\Controllers\Controller;
use App\Models\Quality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => QualityData::collect(Quality::orderBy('sort_order')->get()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:qualities,name'],
            'label' => ['required', 'string', 'max:100'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
            'bitrate' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $quality = Quality::create($validated);

        return $this->success(QualityData::from($quality), null, 201);
    }

    public function show(Quality $quality): JsonResponse
    {
        return $this->success(QualityData::from($quality));
    }

    public function update(Request $request, Quality $quality): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'label' => ['sometimes', 'string', 'max:100'],
            'width' => ['sometimes', 'integer', 'min:1'],
            'height' => ['sometimes', 'integer', 'min:1'],
            'bitrate' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $quality->update(array_filter($validated, fn ($v) => $v !== null));

        return $this->success(QualityData::from($quality->fresh()));
    }

    public function destroy(Quality $quality): JsonResponse
    {
        $quality->delete();

        return $this->success(null, 'Quality deleted.');
    }
}
