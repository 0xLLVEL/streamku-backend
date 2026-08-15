<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class BrowseController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = \Illuminate\Support\Facades\Cache::remember('browse_rows', now()->addMinutes(60), function () {
            $featured = Movie::where('is_featured', true)
                ->orWhereHas('genres')
                ->with('genres')
                ->limit(5)
                ->get();

            $featuredTv = TvShow::where('is_featured', true)
                ->with('genres')
                ->limit(5)
                ->get();

            $popularMovies = Movie::orderByDesc('popularity')
                ->with('genres')
                ->limit(20)
                ->get();

            $topRatedMovies = Movie::orderByDesc('vote_average')
                ->where('vote_count', '>=', 10)
                ->with('genres')
                ->limit(20)
                ->get();

            $recentMovies = Movie::orderByDesc('created_at')
                ->with('genres')
                ->limit(20)
                ->get();

            $popularTvShows = TvShow::orderByDesc('popularity')
                ->with('genres')
                ->limit(20)
                ->get();

            $topRatedTvShows = TvShow::orderByDesc('vote_average')
                ->where('vote_count', '>=', 10)
                ->with('genres')
                ->limit(20)
                ->get();

            return [
                ['title' => 'Featured', 'items' => $featured->merge($featuredTv)],
                ['title' => 'Popular Movies', 'items' => $popularMovies],
                ['title' => 'Top Rated Movies', 'items' => $topRatedMovies],
                ['title' => 'Recently Added', 'items' => $recentMovies],
                ['title' => 'Popular TV Shows', 'items' => $popularTvShows],
                ['title' => 'Top Rated TV Shows', 'items' => $topRatedTvShows],
            ];
        });

        return response()->json(['rows' => $rows]);
    }
}
