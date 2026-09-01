<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreVideoData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\Video;
use Illuminate\Http\JsonResponse;

class VideoController extends Controller
{
    public function index(Movie|TvShow|Episode $parent): JsonResponse
    {
        return $this->success($parent->videos);
    }

    public function store(StoreVideoData $data, Movie|TvShow|Episode $parent): JsonResponse
    {
        $video = $parent->videos()->create($data->toArray());

        return $this->success($video, null, 201);
    }

    public function update(StoreVideoData $data, Movie|TvShow|Episode $parent, Video $video): JsonResponse
    {
        $video->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($video->fresh());
    }

    public function destroy(Movie|TvShow|Episode $parent, Video $video): JsonResponse
    {
        $video->delete();

        return $this->success(null, 'Video removed.');
    }
}
