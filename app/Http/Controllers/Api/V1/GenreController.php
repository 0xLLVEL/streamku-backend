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

        return $this->success(GenreData::collect($genres));
    }

    public function show(Genre $genre): JsonResponse
    {
        $movies = $genre->movies()->orderByDesc('popularity')->limit(20)->get();
        $tvShows = $genre->tvShows()->orderByDesc('popularity')->limit(20)->get();

        return $this->success([
            'genre' => GenreData::from($genre),
            'movies' => $movies,
            'tv_shows' => $tvShows,
        ]);
    }
}
