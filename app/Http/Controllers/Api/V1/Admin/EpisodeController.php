<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreEpisodeData;
use App\Data\Requests\UpdateEpisodeData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    public function all(): JsonResponse
    {
        return response()->json([
            'data' => Episode::with('season.tvShow')->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function index(TvShow $tvShow, Season $season): JsonResponse
    {
        return response()->json([
            'data' => $season->episodes()->orderBy('episode_number')->get(),
        ]);
    }

    public function store(StoreEpisodeData $data, TvShow $tvShow, Season $season): JsonResponse
    {
        $episode = $season->episodes()->create($data->toArray());

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success($episode, null, 201);
    }

    public function show(TvShow $tvShow, Season $season, Episode $episode): JsonResponse
    {
        return $this->success($episode);
    }

    public function update(UpdateEpisodeData $data, TvShow $tvShow, Season $season, Episode $episode): JsonResponse
    {
        $episode->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($episode->fresh());
    }

    public function destroy(TvShow $tvShow, Season $season, Episode $episode): JsonResponse
    {
        $episode->delete();

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success(null, 'Episode deleted.');
    }
}
