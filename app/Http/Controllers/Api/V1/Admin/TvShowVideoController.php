<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreVideoData;
use App\Http\Controllers\Controller;
use App\Models\TvShow;
use App\Models\Video;
use Illuminate\Http\JsonResponse;

class TvShowVideoController extends Controller
{
    public function index(TvShow $tvShow): JsonResponse
    {
        return response()->json(['data' => $tvShow->videos]);
    }

    public function store(StoreVideoData $data, TvShow $tvShow): JsonResponse
    {
        $video = $tvShow->videos()->create($data->toArray());

        return response()->json(['data' => $video], 201);
    }

    public function update(StoreVideoData $data, TvShow $tvShow, Video $video): JsonResponse
    {
        $video->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => $video->fresh()]);
    }

    public function destroy(TvShow $tvShow, Video $video): JsonResponse
    {
        $video->delete();

        return response()->json(['message' => 'Video removed.']);
    }
}
