<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreMovieData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Movie::with('genres');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre'));
            });
        }

        if ($request->filled('year')) {
            $query->whereYear('release_date', $request->input('year'));
        }

        if ($request->filled('language')) {
            $query->where('original_language', $request->input('language'));
        }

        $allowedSorts = ['title', 'release_date', 'views', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        $perPage = $request->input('per_page', 20);

        return $this->success($query->paginate($perPage)->toArray());
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
        $movie->load(['genres', 'cast', 'videos', 'media.quality']);

        return $this->success($movie);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'release_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'original_language' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ]);

        $attributes = array_filter(Arr::except($validated, 'genre_ids'), fn ($v) => $v !== null);

        if (isset($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title'].'-'.($movie->tmdb_id ?? $movie->id));
        }

        $movie->update($attributes);

        if (($validated['genre_ids'] ?? null) !== null) {
            $movie->genres()->sync($validated['genre_ids']);
        }

        return $this->success($movie->fresh(['genres', 'cast', 'videos', 'media.quality']));
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
