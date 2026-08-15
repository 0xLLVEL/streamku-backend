<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreTvShowData;
use App\Data\Requests\UpdateTvShowData;
use App\Http\Controllers\Controller;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TvShowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TvShow::with('genres');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        return response()->json($query->orderByDesc('created_at')->paginate(20));
    }

    public function store(StoreTvShowData $data): JsonResponse
    {
        $tvShow = TvShow::create([
            ...$data->except('genre_ids')->toArray(),
            'slug' => Str::slug($data->name.'-'.Str::random(5)),
        ]);

        if (! empty($data->genre_ids)) {
            $tvShow->genres()->sync($data->genre_ids);
        }

        $tvShow->load('genres');

        return response()->json(['data' => $tvShow], 201);
    }

    public function show(TvShow $tvShow): JsonResponse
    {
        $tvShow->load(['genres', 'cast', 'videos', 'seasons.episodes']);

        return response()->json(['data' => $tvShow]);
    }

    public function update(UpdateTvShowData $data, TvShow $tvShow): JsonResponse
    {
        $attributes = array_filter($data->except('genre_ids')->toArray(), fn ($v) => $v !== null);

        if (isset($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name'].'-'.($tvShow->tmdb_id ?? $tvShow->id));
        }

        $tvShow->update($attributes);

        if ($data->genre_ids !== null) {
            $tvShow->genres()->sync($data->genre_ids);
        }

        return response()->json(['data' => $tvShow->fresh(['genres', 'cast', 'videos', 'seasons'])]);
    }

    public function destroy(TvShow $tvShow): JsonResponse
    {
        $tvShow->delete();

        return response()->json(['message' => 'TV show deleted.']);
    }

    public function toggleFeatured(TvShow $tvShow): JsonResponse
    {
        $tvShow->update(['is_featured' => ! $tvShow->is_featured]);

        return response()->json(['data' => $tvShow->fresh(), 'message' => $tvShow->is_featured ? 'Featured.' : 'Unfeatured.']);
    }
}
