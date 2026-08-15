<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreWatchlistData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;

class WatchlistController extends Controller
{
    public function index(): JsonResponse
    {
        $items = request()->user()
            ->watchlists()
            ->with('watchlistable')
            ->latest()
            ->paginate(20);

        $items->setCollection($items->getCollection()->map(fn($w) => \App\Data\WatchlistData::fromModel($w)));
        return $this->success($items->toArray());
    }

    public function store(StoreWatchlistData $data): JsonResponse
    {
        $morphType = match ($data->watchlistable_type) {
            'movie' => Movie::class,
            'tv_show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return $this->error('Invalid watchlistable type.', 422);
        }

        $item = request()->user()->watchlists()->firstOrCreate([
            'watchlistable_id' => $data->watchlistable_id,
            'watchlistable_type' => $morphType,
        ]);

        $item->load('watchlistable');

        return $this->success(\App\Data\WatchlistData::fromModel($item), null, 201);
    }

    public function destroy(Watchlist $watchlist): JsonResponse
    {
        if ($watchlist->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $watchlist->delete();

        return $this->success(null, 'Removed from watchlist.');
    }
}
