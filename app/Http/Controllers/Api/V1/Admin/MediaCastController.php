<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaCastController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success($this->parent()->cast()->orderBy('order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'character' => ['nullable', 'string', 'max:255'],
            'profile_path' => ['nullable', 'string', 'max:500'],
            'order' => ['integer', 'min:0'],
        ]);

        $cast = $this->parent()->cast()->create($validated);

        return $this->success($cast, null, 201);
    }

    public function update(Request $request): JsonResponse
    {
        $cast = $this->cast();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'character' => ['nullable', 'string', 'max:255'],
            'profile_path' => ['nullable', 'string', 'max:500'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $cast->update(array_filter($validated, fn ($v) => $v !== null));

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
