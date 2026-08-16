<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function overview(): JsonResponse
    {
        $totalUsers = User::count();
        $totalMovies = Movie::count();
        $totalShows = TvShow::count();
        
        $totalWatchSeconds = WatchHistory::sum('progress_seconds');
        $totalWatchHours = round($totalWatchSeconds / 3600, 2);

        $topCountries = WatchHistory::select('country', DB::raw('count(*) as views'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return $this->success([
            'total_users' => $totalUsers,
            'total_movies' => $totalMovies,
            'total_tv_shows' => $totalShows,
            'total_watch_hours' => $totalWatchHours,
            'top_countries' => $topCountries,
        ]);
    }

    public function topTitles(): JsonResponse
    {
        $topMovies = WatchHistory::where('watchable_type', Movie::class)
            ->select('watchable_id', DB::raw('count(*) as views'), DB::raw('sum(progress_seconds) as total_watch_time'))
            ->groupBy('watchable_id')
            ->orderByDesc('views')
            ->with('watchable:id,title,poster_path')
            ->limit(10)
            ->get();

        $topShows = WatchHistory::where('watchable_type', \App\Models\Episode::class)
            ->select('watchable_id', DB::raw('count(*) as views'), DB::raw('sum(progress_seconds) as total_watch_time'))
            ->groupBy('watchable_id')
            ->orderByDesc('views')
            ->with('watchable.season.tvShow:id,name,poster_path')
            ->limit(10)
            ->get();

        return $this->success([
            'top_movies' => $topMovies,
            'top_episodes' => $topShows,
        ]);
    }

    public function engagement(): JsonResponse
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $signups = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $watches = WatchHistory::select(DB::raw('DATE(last_watched_at) as date'), DB::raw('count(*) as count'))
            ->where('last_watched_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');
            
        $reviews = Review::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - $i - 1)->format('Y-m-d');
            $chartData[] = [
                'date' => $date,
                'signups' => $signups->get($date)?->count ?? 0,
                'watches' => $watches->get($date)?->count ?? 0,
                'reviews' => $reviews->get($date)?->count ?? 0,
            ];
        }

        return $this->success([
            'chart_data' => $chartData,
        ]);
    }
}
