<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreEpisodeData;
use App\Data\Requests\UpdateEpisodeData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
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

    public function index(TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();

        return response()->json([
            'data' => $season->episodes()->orderBy('episode_number')->get(),
        ]);
    }

    public function store(StoreEpisodeData $data, TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->create($data->toArray());

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success($episode, null, 201);
    }

    public function show(TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();
        $episode->load(['media.quality', 'videos', 'season.tvShow']);

        return $this->success($episode);
    }

    public function update(UpdateEpisodeData $data, TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();
        $episode->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($episode->fresh(['media.quality', 'videos', 'season.tvShow']));
    }

    public function destroy(TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();
        $episode->delete();

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success(null, 'Episode deleted.');
    }
}
