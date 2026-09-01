<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BrowseController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = Cache::remember('browse_rows', now()->addMinutes(5), function () {
            $featured = Movie::with('genres')->featured()->limit(5)->get();

            $featuredTv = TvShow::with('genres')->featured()->limit(5)->get();

            $popularMovies = Movie::with('genres')->popular()->limit(20)->get();

            $topRatedMovies = Movie::with('genres')->topRated()->limit(20)->get();

            $recentMovies = Movie::with('genres')->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $recentTvShows = TvShow::with('genres')->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $popularTvShows = TvShow::with('genres')->popular()->limit(20)->get();

            $topRatedTvShows = TvShow::with('genres')->topRated()->limit(20)->get();

            return [
                ['title' => 'Featured', 'items' => $featured->concat($featuredTv)->values()->toArray()],
                ['title' => 'Popular Movies', 'items' => $popularMovies->toArray()],
                ['title' => 'Top Rated Movies', 'items' => $topRatedMovies->toArray()],
                ['title' => 'Recently Added', 'items' => $recentMovies->concat($recentTvShows)->sortByDesc('created_at')->values()->toArray()],
                ['title' => 'Popular TV Shows', 'items' => $popularTvShows->toArray()],
                ['title' => 'Top Rated TV Shows', 'items' => $topRatedTvShows->toArray()],
            ];
        });

        return $this->success(['rows' => $rows]);
    }
}
