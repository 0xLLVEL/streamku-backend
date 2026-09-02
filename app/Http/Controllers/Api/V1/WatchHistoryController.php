<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreWatchHistoryData;
use App\Data\WatchHistoryData;
use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
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
        $morphType = MediaType::fromString($data->media_type);

        if (! $morphType) {
            return $this->error('Invalid media type.', 422);
        }

        $ip = request()->ip();
        $position = Location::get($ip);

        $history = request()->user()->watchHistories()->updateOrCreate(
            [
                'watchable_id' => $data->media_id,
                'watchable_type' => $morphType->modelClass(),
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
