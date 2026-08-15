<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreMovieData;
use App\Data\Requests\UpdateMovieData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Movie::with('genres');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        return $this->success($query->orderByDesc('created_at')->paginate(20)->toArray());
    }

    public function store(StoreMovieData $data): JsonResponse
    {
        $movie = Movie::create([
            ...$data->except('genre_ids')->toArray(),
            'slug' => Str::slug($data->title.'-'.Str::random(5)),
        ]);

        if (! empty($data->genre_ids)) {
            $movie->genres()->sync($data->genre_ids);
        }

        $movie->load('genres');

        return $this->success($movie, null, 201);
    }

    public function show(Movie $movie): JsonResponse
    {
        $movie->load(['genres', 'cast', 'videos']);

        return $this->success($movie);
    }

    public function update(UpdateMovieData $data, Movie $movie): JsonResponse
    {
        $attributes = array_filter($data->except('genre_ids')->toArray(), fn ($v) => $v !== null);

        if (isset($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title'].'-'.($movie->tmdb_id ?? $movie->id));
        }

        $movie->update($attributes);

        if ($data->genre_ids !== null) {
            $movie->genres()->sync($data->genre_ids);
        }

        return $this->success($movie->fresh(['genres', 'cast', 'videos']));
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $movie->delete();

        return $this->success(null, 'Movie deleted.');
    }

    public function toggleFeatured(Movie $movie): JsonResponse
    {
        $movie->update(['is_featured' => ! $movie->is_featured]);

        return $this->success($movie->fresh(), $movie->is_featured ? 'Featured.' : 'Unfeatured.');
    }
}
