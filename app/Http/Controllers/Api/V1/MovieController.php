<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\MovieData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Movie::with('genres');

        if ($request->filled('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('slug', $request->input('genre')));
        }

        if ($request->filled('year')) {
            $query->whereYear('release_date', $request->input('year'));
        }

        $sortField = match ($request->input('sort')) {
            'title' => 'title',
            'rating' => 'vote_average',
            'release_date' => 'release_date',
            'created_at' => 'created_at',
            default => 'popularity',
        };

        $query->orderByDesc($sortField);

        $movies = $query->paginate($request->integer('per_page', 20));

        return response()->json($movies);
    }

    public function show(Movie $movie): JsonResponse
    {
        $movie->load(['genres', 'cast', 'videos']);

        $data = MovieData::from([
            ...$movie->toArray(),
            'genres' => $movie->genres->toArray(),
            'cast' => $movie->cast->toArray(),
            'videos' => $movie->videos->toArray(),
        ]);

        return response()->json(['data' => $data]);
    }
}
