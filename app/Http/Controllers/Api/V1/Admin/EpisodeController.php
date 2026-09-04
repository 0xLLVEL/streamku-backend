<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(Request $request, TvShow $tvShow, int $season_number): JsonResponse
    {
        $validated = $request->validate([
            'episode_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
        ]);

        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->create($validated);

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success($episode, null, 201);
    }

    public function bulkVidking(Request $request, TvShow $tvShow, int $season_number): JsonResponse
    {
        $validated = $request->validate([
            'total_episodes' => 'required|integer|min:1|max:1000',
            'site' => 'sometimes|string|max:50|in:VidKing,VixSrc,VidSrcCc,VidSrcMe,SuperEmbed,2Embed,EmbedSu,AutoEmbed,VidLink',
        ]);

        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $total = $request->integer('total_episodes');
        $site = $validated['site'] ?? 'VidKing';
        $key = (string) ($tvShow->tmdb_id ?? $tvShow->slug);

        for ($i = 1; $i <= $total; $i++) {
            $episode = $season->episodes()->firstOrCreate(
                ['episode_number' => $i],
                ['name' => "Episode $i", 'tmdb_id' => null]
            );

            $episode->videos()->firstOrCreate(
                ['site' => $site],
                [
                    'key' => $key,
                    'name' => "Episode $i",
                    'official' => false,
                ]
            );
        }

        $season->update(['episode_count' => $season->episodes()->count()]);
        $tvShow->update(['number_of_episodes' => $tvShow->seasons()->withCount('episodes')->get()->sum('episodes_count')]);

        return $this->success(['message' => 'Episodes created successfully.']);
    }

    public function show(TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();
        $episode->load(['media.quality', 'videos', 'season.tvShow']);

        return $this->success($episode);
    }

    public function update(Request $request, TvShow $tvShow, int $season_number, int $episode_number): JsonResponse
    {
        $validated = $request->validate([
            'episode_number' => ['sometimes', 'integer', 'min:1'],
            'name' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'air_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
        ]);

        $season = $tvShow->seasons()->where('season_number', $season_number)->firstOrFail();
        $episode = $season->episodes()->where('episode_number', $episode_number)->firstOrFail();
        $episode->update(array_filter($validated, fn ($v) => $v !== null));

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
