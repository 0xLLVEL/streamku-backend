<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\QualityData;
use App\Data\Requests\StoreQualityData;
use App\Data\Requests\UpdateQualityData;
use App\Http\Controllers\Controller;
use App\Models\Quality;
use Illuminate\Http\JsonResponse;

class QualityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => QualityData::collect(Quality::orderBy('sort_order')->get()),
        ]);
    }

    public function store(StoreQualityData $data): JsonResponse
    {
        $quality = Quality::create($data->toArray());

        return response()->json(['data' => QualityData::from($quality)], 201);
    }

    public function show(Quality $quality): JsonResponse
    {
        return response()->json(['data' => QualityData::from($quality)]);
    }

    public function update(UpdateQualityData $data, Quality $quality): JsonResponse
    {
        $quality->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => QualityData::from($quality->fresh())]);
    }

    public function destroy(Quality $quality): JsonResponse
    {
        $quality->delete();

        return response()->json(['message' => 'Quality deleted.']);
    }
}
