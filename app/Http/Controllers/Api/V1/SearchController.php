<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q');

        if (! $q) {
            return $this->success([
                'movies' => [],
                'tv_shows' => [],
            ]);
        }

        $q = addcslashes($q, '%_\\');

        $movies = Movie::where('title', 'like', "%{$q}%")
            ->orderBy('popularity', 'desc')
            ->limit(20)
            ->get();

        $tvShows = TvShow::where('name', 'like', "%{$q}%")
            ->orderBy('popularity', 'desc')
            ->limit(20)
            ->get();

        return $this->success([
            'movies' => $movies,
            'tv_shows' => $tvShows,
        ]);
    }
}
