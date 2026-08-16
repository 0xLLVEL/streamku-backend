<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreSeasonData;
use App\Data\Requests\UpdateSeasonData;
use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class SeasonController extends Controller
{
    public function all(): JsonResponse
    {
        return response()->json([
            'data' => Season::with('tvShow')->orderByDesc('created_at')->paginate(20),
        ]);
    }
    public function index(TvShow $tvShow): JsonResponse
    {
        return response()->json([
            'data' => $tvShow->seasons()->orderBy('season_number')->get(),
        ]);
    }

    public function store(StoreSeasonData $data, TvShow $tvShow): JsonResponse
    {
        $season = $tvShow->seasons()->create($data->toArray());

        $tvShow->update(['number_of_seasons' => $tvShow->seasons()->count()]);

        return $this->success($season, null, 201);
    }

    public function show(TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $season->load('episodes');

        return $this->success($season);
    }

    public function update(UpdateSeasonData $data, TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $season->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($season->fresh());
    }

    public function destroy(TvShow $tvShow, int $season_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $season->delete();

        $tvShow->update(['number_of_seasons' => $tvShow->seasons()->count()]);

        return $this->success(null, 'Season deleted.');
    }
}
