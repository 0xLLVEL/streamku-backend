<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreWatchHistoryData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;

class WatchHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $history = request()->user()
            ->watchHistories()
            ->with('watchable')
            ->latest('last_watched_at')
            ->paginate(20);

        $history->setCollection($history->getCollection()->map(fn($h) => \App\Data\WatchHistoryData::fromModel($h)));
        return $this->success($history->toArray());
    }

    public function store(StoreWatchHistoryData $data): JsonResponse
    {
        $morphType = match ($data->watchable_type) {
            'movie' => Movie::class,
            'episode' => Episode::class,
            default => null,
        };

        if (! $morphType) {
            return $this->error('Invalid watchable type.', 422);
        }

        $history = request()->user()->watchHistories()->updateOrCreate(
            [
                'watchable_id' => $data->watchable_id,
                'watchable_type' => $morphType,
            ],
            [
                'progress_seconds' => $data->progress_seconds,
                'duration_seconds' => $data->duration_seconds,
                'completed' => $data->completed,
                'last_watched_at' => now(),
            ]
        );

        return $this->success(\App\Data\WatchHistoryData::fromModel($history));
    }

    public function continueWatching(): JsonResponse
    {
        $items = request()->user()
            ->watchHistories()
            ->with('watchable')
            ->where('completed', false)
            ->where('progress_seconds', '>', 0)
            ->latest('last_watched_at')
            ->limit(20)
            ->get();

        return $this->success(\App\Data\WatchHistoryData::collect(
            $items->map(fn($h) => \App\Data\WatchHistoryData::fromModel($h))
        ));
    }
}
