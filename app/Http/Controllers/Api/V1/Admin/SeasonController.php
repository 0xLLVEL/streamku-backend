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

        return response()->json(['data' => $season], 201);
    }

    public function show(TvShow $tvShow, Season $season): JsonResponse
    {
        $season->load('episodes');

        return response()->json(['data' => $season]);
    }

    public function update(UpdateSeasonData $data, TvShow $tvShow, Season $season): JsonResponse
    {
        $season->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => $season->fresh()]);
    }

    public function destroy(TvShow $tvShow, Season $season): JsonResponse
    {
        $season->delete();

        $tvShow->update(['number_of_seasons' => $tvShow->seasons()->count()]);

        return response()->json(['message' => 'Season deleted.']);
    }
}
