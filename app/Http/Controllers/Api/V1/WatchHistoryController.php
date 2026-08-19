<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreWatchHistoryData;
use App\Data\WatchHistoryData;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class WatchHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $history = request()->user()
            ->watchHistories()
            ->with('watchable')
            ->latest('last_watched_at')
            ->paginate(20);

        $history->setCollection($history->getCollection()->map(fn ($h) => WatchHistoryData::fromModel($h)));

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

        $ip = request()->ip();
        $position = Location::get($ip);

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
                'ip_address' => $ip,
                'country' => $position ? $position->countryCode : null,
            ]
        );

        return $this->success(WatchHistoryData::fromModel($history));
    }

    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'watchable_type' => 'required|string|in:movie,episode',
            'watchable_id' => 'required|integer',
            'progress_seconds' => 'required|integer|min:0',
            'completed' => 'required|boolean',
        ]);

        $morphType = match ($validated['watchable_type']) {
            'movie' => Movie::class,
            'episode' => Episode::class,
        };

        $updated = request()->user()->watchHistories()
            ->where('watchable_id', $validated['watchable_id'])
            ->where('watchable_type', $morphType)
            ->update([
                'progress_seconds' => $validated['progress_seconds'],
                'completed' => $validated['completed'],
                'last_watched_at' => now(),
            ]);

        if (! $updated) {
            return $this->error('Watch history not found. Call POST /history first.', 404);
        }

        return $this->success(null, 'Progress synced successfully.');
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

        return $this->success(WatchHistoryData::collect(
            $items->map(fn ($h) => WatchHistoryData::fromModel($h))
        ));
    }
}
