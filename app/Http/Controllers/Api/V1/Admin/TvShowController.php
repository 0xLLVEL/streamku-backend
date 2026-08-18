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

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre'));
            });
        }

        if ($request->filled('year')) {
            $query->whereYear('first_air_date', $request->input('year'));
        }

        if ($request->filled('language')) {
            $query->where('original_language', $request->input('language'));
        }

        $allowedSorts = ['name', 'first_air_date', 'views', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        $perPage = $request->input('per_page', 20);

        return $this->success($query->paginate($perPage)->toArray());
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

        return $this->success($tvShow, null, 201);
    }

    public function show(TvShow $tvShow): JsonResponse
    {
        $tvShow->load(['genres', 'cast', 'videos', 'seasons.episodes']);

        return $this->success($tvShow);
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

        return $this->success($tvShow->fresh(['genres', 'cast', 'videos', 'seasons']));
    }

    public function destroy(TvShow $tvShow): JsonResponse
    {
        $tvShow->delete();

        return $this->success(null, 'TV show deleted.');
    }

    public function toggleFeatured(TvShow $tvShow): JsonResponse
    {
        $tvShow->update(['is_featured' => ! $tvShow->is_featured]);

        return $this->success($tvShow->fresh(), $tvShow->is_featured ? 'Featured.' : 'Unfeatured.');
    }
}
