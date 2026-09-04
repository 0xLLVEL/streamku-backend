<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success($this->parent()->videos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'site' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'official' => ['boolean'],
        ]);

        $video = $this->parent()->videos()->create($validated);

        return $this->success($video, null, 201);
    }

    public function update(Request $request): JsonResponse
    {
        $video = $this->video();

        $validated = $request->validate([
            'key' => ['sometimes', 'string', 'max:255'],
            'site' => ['sometimes', 'string', 'max:50'],
            'name' => ['sometimes', 'string', 'max:255'],
            'official' => ['boolean'],
        ]);

        $video->update(array_filter($validated, fn ($v) => $v !== null));

        return $this->success($video->fresh());
    }

    public function destroy(): JsonResponse
    {
        $this->video()->delete();

        return $this->success(null, 'Video removed.');
    }

    /**
     * Resolve the parent media model from the current route (movie, tv-show, or episode).
     */
    protected function parent(): Movie|TvShow|Episode
    {
        $route = request()->route();

        if ($route->hasParameter('episode_number')) {
            $tvShow = TvShow::findOrFail($route->parameter('tvShow'));

            return $tvShow->seasons()
                ->where('season_number', $route->parameter('season_number'))
                ->firstOrFail()
                ->episodes()
                ->where('episode_number', $route->parameter('episode_number'))
                ->firstOrFail();
        }

        if ($route->hasParameter('tvShow')) {
            return TvShow::findOrFail($route->parameter('tvShow'));
        }

        return Movie::findOrFail($route->parameter('movie'));
    }

    /**
     * Resolve the child video by its route parameter (global, not scoped to parent).
     */
    protected function video(): Video
    {
        return Video::findOrFail(request()->route('video'));
    }
}
