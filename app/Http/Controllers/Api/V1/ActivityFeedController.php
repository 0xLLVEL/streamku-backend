<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $friendIds = $request->user()->friends()->pluck('users.id');

        if ($friendIds->isEmpty()) {
            return $this->success([]);
        }

        $dateLimit = now()->subDays(7);

        // 1. Get recent completed watches
        $watches = WatchHistory::with(['user:id,name', 'watchable'])
            ->whereIn('user_id', $friendIds)
            ->where('completed', true)
            ->where('updated_at', '>=', $dateLimit)
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'watch',
                    'user' => $item->user,
                    'watchable' => $item->watchable,
                    'created_at' => $item->updated_at,
                ];
            });

        // 2. Get recent reviews
        $reviews = Review::with(['user:id,name', 'reviewable'])
            ->whereIn('user_id', $friendIds)
            ->where('created_at', '>=', $dateLimit)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'review',
                    'user' => $item->user,
                    'reviewable' => $item->reviewable,
                    'rating' => $item->rating,
                    'body' => $item->body,
                    'created_at' => $item->created_at,
                ];
            });

        // 3. Get recent watchlists
        $watchlists = Watchlist::with(['user:id,name', 'watchlistable'])
            ->whereIn('user_id', $friendIds)
            ->where('created_at', '>=', $dateLimit)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'watchlist',
                    'user' => $item->user,
                    'watchlistable' => $item->watchlistable,
                    'created_at' => $item->created_at,
                ];
            });

        // Merge and sort
        $feed = collect()
            ->merge($watches)
            ->merge($reviews)
            ->merge($watchlists)
            ->sortByDesc('created_at')
            ->values();

        return $this->success($feed);
    }
}
