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
    public function index(): JsonResponse
    {
        return $this->success($this->parent()->cast()->orderBy('order')->get());
    }

    public function store(StoreCastData $data): JsonResponse
    {
        $cast = $this->parent()->cast()->create($data->toArray());

        return $this->success($cast, null, 201);
    }

    public function update(StoreCastData $data): JsonResponse
    {
        $cast = $this->cast();

        $cast->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($cast->fresh());
    }

    public function destroy(): JsonResponse
    {
        $this->cast()->delete();

        return $this->success(null, 'Cast member removed.');
    }

    /**
     * Resolve the parent media model from the current route (movie or tv-show).
     */
    protected function parent(): Movie|TvShow
    {
        $route = request()->route();

        if ($route->hasParameter('tvShow')) {
            return TvShow::findOrFail($route->parameter('tvShow'));
        }

        return Movie::findOrFail($route->parameter('movie'));
    }

    /**
     * Resolve the child cast member by its route parameter.
     */
    protected function cast(): Cast
    {
        return Cast::findOrFail(request()->route('cast'));
    }
}
