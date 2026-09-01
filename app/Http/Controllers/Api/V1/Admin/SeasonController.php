<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreSeasonData;
use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function update(Request $request, TvShow $tvShow, int $season_number): JsonResponse
    {
        $validated = $request->validate([
            'season_number' => ['sometimes', 'integer', 'min:0'],
            'name' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
        ]);

        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $season->update(array_filter($validated, fn ($v) => $v !== null));

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
