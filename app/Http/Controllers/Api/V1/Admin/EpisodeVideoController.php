<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreVideoData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Video;
use Illuminate\Http\JsonResponse;

class EpisodeVideoController extends Controller
{
    public function index(Episode $episode): JsonResponse
    {
        return $this->success($episode->videos);
    }

    public function store(StoreVideoData $data, Episode $episode): JsonResponse
    {
        $video = $episode->videos()->create($data->toArray());
        return $this->success($video, null, 201);
    }

    public function update(StoreVideoData $data, Episode $episode, Video $video): JsonResponse
    {
        $video->update(array_filter($data->toArray(), fn ($v) => $v !== null));
        return $this->success($video->fresh());
    }

    public function destroy(Episode $episode, Video $video): JsonResponse
    {
        $video->delete();
        return $this->success(null, 'Video removed.');
    }
}
