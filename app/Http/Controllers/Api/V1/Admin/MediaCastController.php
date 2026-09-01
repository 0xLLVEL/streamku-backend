<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreCastData;
use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class MediaCastController extends Controller
{
    public function index(Movie|TvShow $parent): JsonResponse
    {
        return $this->success($parent->cast()->orderBy('order')->get());
    }

    public function store(StoreCastData $data, Movie|TvShow $parent): JsonResponse
    {
        $cast = $parent->cast()->create($data->toArray());

        return $this->success($cast, null, 201);
    }

    public function update(StoreCastData $data, Movie|TvShow $parent, Cast $cast): JsonResponse
    {
        $cast->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($cast->fresh());
    }

    public function destroy(Movie|TvShow $parent, Cast $cast): JsonResponse
    {
        $cast->delete();

        return $this->success(null, 'Cast member removed.');
    }
}
