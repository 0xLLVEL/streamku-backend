<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreVideoData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Video;
use Illuminate\Http\JsonResponse;

class MovieVideoController extends Controller
{
    public function index(Movie $movie): JsonResponse
    {
        return $this->success($movie->videos);
    }

    public function store(StoreVideoData $data, Movie $movie): JsonResponse
    {
        $video = $movie->videos()->create($data->toArray());

        return $this->success($video, null, 201);
    }

    public function update(StoreVideoData $data, Movie $movie, Video $video): JsonResponse
    {
        $video->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success($video->fresh());
    }

    public function destroy(Movie $movie, Video $video): JsonResponse
    {
        $video->delete();

        return $this->success(null, 'Video removed.');
    }
}
