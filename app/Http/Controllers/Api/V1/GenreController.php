<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\GenreData;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        $genres = Genre::orderBy('name')->get();

        return response()->json([
            'data' => GenreData::collect($genres),
        ]);
    }

    public function show(Genre $genre): JsonResponse
    {
        $movies = $genre->movies()->with('genres')->orderByDesc('popularity')->limit(20)->get();
        $tvShows = $genre->tvShows()->with('genres')->orderByDesc('popularity')->limit(20)->get();

        return response()->json([
            'data' => GenreData::from($genre),
            'movies' => $movies,
            'tv_shows' => $tvShows,
        ]);
    }
}
