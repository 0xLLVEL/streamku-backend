<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\MovieData;
use App\Data\WatchHistoryData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'movies_index_'.md5(json_encode($request->all()));

        $movies = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Movie::query();

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

            return $query->paginate($request->integer('per_page', 20))->toArray();
        });

        return $this->success($movies);
    }

    public function show(Movie $movie): JsonResponse
    {
        $movie->load(['genres', 'cast', 'videos']);

        if ($user = request()->user('sanctum')) {
            $movie->load(['watchHistories' => fn ($q) => $q->where('user_id', $user->id)]);
        }

        $data = MovieData::from([
            ...$movie->toArray(),
            'genres' => $movie->genres->toArray(),
            'cast' => $movie->cast->toArray(),
            'videos' => $movie->videos->toArray(),
            'history' => $movie->watchHistories?->first() ? WatchHistoryData::fromModel($movie->watchHistories->first()) : null,
        ]);

        return $this->success($data);
    }

    public function recommendations(Movie $movie): JsonResponse
    {
        $genreIds = $movie->genres()->pluck('genres.id');

        $recommendations = Movie::where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($genreIds) {
                $query->whereIn('genres.id', $genreIds);
            })
            ->orderByDesc('popularity')
            ->limit(10)
            ->get();

        return $this->success($recommendations->values());
    }
}
