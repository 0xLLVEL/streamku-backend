<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreCastData;
use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;

class MovieCastController extends Controller
{
    public function index(Movie $movie): JsonResponse
    {
        return response()->json(['data' => $movie->cast()->orderBy('order')->get()]);
    }

    public function store(StoreCastData $data, Movie $movie): JsonResponse
    {
        $cast = $movie->cast()->create($data->toArray());

        return response()->json(['data' => $cast], 201);
    }

    public function update(StoreCastData $data, Movie $movie, Cast $cast): JsonResponse
    {
        $cast->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => $cast->fresh()]);
    }

    public function destroy(Movie $movie, Cast $cast): JsonResponse
    {
        $cast->delete();

        return response()->json(['message' => 'Cast member removed.']);
    }
}
