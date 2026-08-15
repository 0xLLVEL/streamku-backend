<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreCastData;
use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class TvShowCastController extends Controller
{
    public function index(TvShow $tvShow): JsonResponse
    {
        return response()->json(['data' => $tvShow->cast()->orderBy('order')->get()]);
    }

    public function store(StoreCastData $data, TvShow $tvShow): JsonResponse
    {
        $cast = $tvShow->cast()->create($data->toArray());

        return response()->json(['data' => $cast], 201);
    }

    public function update(StoreCastData $data, TvShow $tvShow, Cast $cast): JsonResponse
    {
        $cast->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => $cast->fresh()]);
    }

    public function destroy(TvShow $tvShow, Cast $cast): JsonResponse
    {
        $cast->delete();

        return response()->json(['message' => 'Cast member removed.']);
    }
}
