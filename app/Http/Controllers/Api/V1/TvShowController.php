<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TvShowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TvShow::query();

        if ($request->filled('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('slug', $request->input('genre')));
        }

        if ($request->filled('year')) {
            $query->whereYear('first_air_date', $request->input('year'));
        }

        $sortField = match ($request->input('sort')) {
            'name' => 'name',
            'rating' => 'vote_average',
            'first_air_date' => 'first_air_date',
            'created_at' => 'created_at',
            default => 'popularity',
        };

        $query->orderByDesc($sortField);

        return $this->success($query->paginate($request->integer('per_page', 20))->toArray());
    }

    public function show(TvShow $tvShow): JsonResponse
    {
        $tvShow->load(['genres', 'cast', 'videos', 'seasons.episodes']);

        return $this->success(\App\Data\TvShowData::from($tvShow));
    }

    public function season(TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->with('episodes')->firstOrFail();

        return $this->success(\App\Data\SeasonData::from($season));
    }

    public function episode(TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();

        return $this->success(\App\Data\EpisodeData::from($episode));
    }
}
