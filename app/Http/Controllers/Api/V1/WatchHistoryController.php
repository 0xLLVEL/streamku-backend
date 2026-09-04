<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $history = request()->user()
            ->watchHistories()
            ->with('watchable')
            ->latest('last_watched_at')
            ->paginate(20);

        $history->setCollection($history->getCollection()->map(fn ($h) => $h->toApiArray()));

        return $this->success($history->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'media_id' => ['required', 'integer'],
            'media_type' => ['required', 'string', 'in:movie,episode'],
            'progress_seconds' => ['integer', 'min:0'],
            'duration_seconds' => ['integer', 'min:0'],
            'completed' => ['boolean'],
        ]);

        $morphType = MediaType::tryFrom($validated['media_type']);

        if (! $morphType) {
            return $this->error('Invalid media type.', 422);
        }

        $ip = request()->ip();

        $history = request()->user()->watchHistories()->updateOrCreate(
            [
                'watchable_id' => $validated['media_id'],
                'watchable_type' => $morphType->modelClass(),
            ],
            [
                'progress_seconds' => $validated['progress_seconds'] ?? 0,
                'duration_seconds' => $validated['duration_seconds'] ?? 0,
                'completed' => $validated['completed'] ?? false,
                'last_watched_at' => now(),
                'ip_address' => $ip,
                'country' => null,
            ]
        );

        return $this->success($history->toApiArray());
    }

    public function continueWatching(): JsonResponse
    {
        $items = request()->user()
            ->watchHistories()
            ->with(['watchable' => function ($morphTo) {
                $morphTo->morphWith([
                    Episode::class => ['season.tvShow'],
                ]);
            }])
            ->where('completed', false)
            ->where('progress_seconds', '>', 0)
            ->latest('last_watched_at')
            ->limit(20)
            ->get();

        return $this->success($items->map->toApiArray());
    }
}
