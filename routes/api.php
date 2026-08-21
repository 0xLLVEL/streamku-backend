<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WatchPartyController;
use App\Http\Controllers\Api\V1\BrowseController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\MediaStreamController;
use App\Http\Controllers\Api\V1\MovieController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\TvShowController;
use App\Http\Controllers\Api\V1\WatchHistoryController;
use App\Http\Controllers\Api\V1\WatchlistController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Auth (public) ───────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // ── Media & Streaming (public) ──────────────────────────
    Route::get('/movies/{movie:slug}/media', [MediaStreamController::class, 'movieMedia'])->name('movies.media');
    Route::get('/tv-shows/{tvShow:slug}/seasons/{season:season_number}/episodes/{episode:episode_number}/media', [MediaStreamController::class, 'episodeMedia'])->name('episodes.media');
    Route::get('/media/{media}/stream', [MediaStreamController::class, 'stream'])->name('media.stream');

    // ── Authenticated ───────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('/auth/me', [AuthController::class, 'updateProfile'])->name('auth.update');

        // Browse
        Route::get('/browse', [BrowseController::class, 'index'])->name('browse');

        // Movies
        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
        Route::get('/movies/{movie:slug}/recommendations', [MovieController::class, 'recommendations'])->name('movies.recommendations');

        // TV Shows
        Route::get('/tv-shows', [TvShowController::class, 'index'])->name('tv-shows.index');
        Route::get('/tv-shows/{tvShow:slug}', [TvShowController::class, 'show'])->name('tv-shows.show');
        Route::get('/tv-shows/{tvShow:slug}/recommendations', [TvShowController::class, 'recommendations'])->name('tv-shows.recommendations');
        Route::get('/tv-shows/{tvShow:slug}/seasons/{season_number}', [TvShowController::class, 'season'])->name('tv-shows.seasons.show');
        Route::get('/tv-shows/{tvShow:slug}/seasons/{season_number}/episodes/{episode_number}', [TvShowController::class, 'episode'])->name('tv-shows.seasons.episodes.show');

        // Genres
        Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
        Route::get('/genres/{genre:slug}', [GenreController::class, 'show'])->name('genres.show');

        // Cast
        Route::get('/cast', [Admin\CastController::class, 'index'])->name('cast.index');

        // Watchlist
        Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
        Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
        Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');

        // Favorites
        Route::get('/favorites', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{favorite}', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'destroy'])->name('favorites.destroy');

        // User Profile
        Route::get('/users/{user}/profile', [\App\Http\Controllers\Api\V1\UserProfileController::class, 'show'])->name('users.profile');

        // Watch History
        Route::get('/history', [WatchHistoryController::class, 'index'])->name('history.index');
        Route::post('/history', [WatchHistoryController::class, 'store'])->name('history.store');
        Route::patch('/history/sync', [WatchHistoryController::class, 'sync'])->name('history.sync');
        Route::get('/history/continue-watching', [WatchHistoryController::class, 'continueWatching'])->name('history.continue');

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/reviews/{type}/{id}', [ReviewController::class, 'forTitle'])->name('reviews.for-title');

        // Watch Parties
        Route::post('/watch-parties', [WatchPartyController::class, 'store'])->name('watch-parties.store');
        Route::get('/watch-parties/{watchParty}', [WatchPartyController::class, 'show'])->name('watch-parties.show');
        Route::post('/watch-parties/{watchParty}/join', [WatchPartyController::class, 'join'])->name('watch-parties.join');
        Route::post('/watch-parties/{watchParty}/sync', [WatchPartyController::class, 'sync'])->name('watch-parties.sync');

        // Friends & Activity
        Route::prefix('friends')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\FriendController::class, 'index'])->name('friends.index');
            Route::get('/pending', [\App\Http\Controllers\Api\V1\FriendController::class, 'pending'])->name('friends.pending');
            Route::post('/request', [\App\Http\Controllers\Api\V1\FriendController::class, 'request'])->name('friends.request');
            Route::post('/{friend}/accept', [\App\Http\Controllers\Api\V1\FriendController::class, 'accept'])->name('friends.accept');
            Route::delete('/{friend}', [\App\Http\Controllers\Api\V1\FriendController::class, 'remove'])->name('friends.remove');
        });
        Route::get('/activity-feed', [\App\Http\Controllers\Api\V1\ActivityFeedController::class, 'index'])->name('activity-feed');

        // ── Admin ───────────────────────────────────────────
        Route::prefix('admin')->middleware(EnsureUserIsAdmin::class)->group(function () {

            // Admin Analytics
            Route::prefix('analytics')->group(function () {
                Route::get('/overview', [Admin\AnalyticsController::class, 'overview'])->name('admin.analytics.overview');
                Route::get('/top-titles', [Admin\AnalyticsController::class, 'topTitles'])->name('admin.analytics.top-titles');
                Route::get('/engagement', [Admin\AnalyticsController::class, 'engagement'])->name('admin.analytics.engagement');
            });

            // Admin Genres
            Route::apiResource('genres', Admin\GenreController::class)->names('admin.genres')->parameters(['genres' => 'genre:slug'])->except(['show']);

            // Admin Cast (Global)
            Route::apiResource('cast', Admin\CastController::class)->names('admin.cast')->except(['show']);
            Route::apiResource('movies', Admin\MovieController::class)->names('admin.movies');
            Route::patch('/movies/{movie}/feature', [Admin\MovieController::class, 'toggleFeatured'])->name('admin.movies.feature');

            // Admin Movie Cast & Videos
            Route::apiResource('movies.cast', Admin\MovieCastController::class)->names('admin.movies.cast')->except(['show']);
            Route::apiResource('movies.videos', Admin\MovieVideoController::class)->names('admin.movies.videos')->except(['show']);

            // Admin TV Shows
            Route::apiResource('tv-shows', Admin\TvShowController::class)->names('admin.tv-shows')->parameters(['tv-shows' => 'tvShow']);
            Route::patch('/tv-shows/{tvShow}/feature', [Admin\TvShowController::class, 'toggleFeatured'])->name('admin.tv-shows.feature');

            // Admin TV Show Cast & Videos
            Route::apiResource('tv-shows.cast', Admin\TvShowCastController::class)->names('admin.tv-shows.cast')->parameters(['tv-shows' => 'tvShow'])->except(['show']);
            Route::apiResource('tv-shows.videos', Admin\TvShowVideoController::class)->names('admin.tv-shows.videos')->parameters(['tv-shows' => 'tvShow'])->except(['show']);

            // Admin Seasons
            Route::get('/seasons', [Admin\SeasonController::class, 'all'])->name('admin.seasons.all');
            Route::apiResource('tv-shows.seasons', Admin\SeasonController::class)->names('admin.tv-shows.seasons')->parameters(['tv-shows' => 'tvShow', 'seasons' => 'season_number']);

            // Admin Episodes
            Route::post('/tv-shows/{tvShow}/seasons/{season_number}/episodes/bulk-vidking', [Admin\EpisodeController::class, 'bulkVidking'])->name('admin.episodes.bulk-vidking');
            Route::get('/episodes', [Admin\EpisodeController::class, 'all'])->name('admin.episodes.all');
            Route::apiResource('tv-shows.seasons.episodes', Admin\EpisodeController::class)->names('admin.tv-shows.seasons.episodes')->parameters(['tv-shows' => 'tvShow', 'seasons' => 'season_number', 'episodes' => 'episode_number']);
            Route::apiResource('episodes.videos', Admin\EpisodeVideoController::class)->names('admin.episodes.videos')->except(['show']);

            // Uploads (Chunked)
            Route::get('/uploads', [Admin\UploadController::class, 'index'])->name('admin.uploads.index');
            Route::post('/uploads/initiate', [Admin\UploadController::class, 'initiate'])->name('admin.uploads.initiate');
            Route::post('/uploads/{upload:upload_id}/chunks', [Admin\UploadController::class, 'chunk'])->name('admin.uploads.chunk');
            Route::get('/uploads/{upload:upload_id}/status', [Admin\UploadController::class, 'status'])->name('admin.uploads.status');
            Route::post('/uploads/{upload:upload_id}/complete', [Admin\UploadController::class, 'complete'])->name('admin.uploads.complete');
            Route::delete('/uploads/{upload:upload_id}', [Admin\UploadController::class, 'cancel'])->name('admin.uploads.cancel');

            // Qualities
            Route::apiResource('qualities', Admin\QualityController::class)->names('admin.qualities');

            // Media Management
            Route::get('/movies/{movie}/media', [Admin\MediaController::class, 'movieMedia'])->name('admin.movies.media');
            Route::get('/tv-shows/{tvShow}/seasons/{season}/episodes/{episode}/media', [Admin\MediaController::class, 'episodeMedia'])->name('admin.episodes.media');
            Route::delete('/media/{media}', [Admin\MediaController::class, 'destroy'])->name('admin.media.destroy');
            Route::patch('/media/{media}/primary', [Admin\MediaController::class, 'setPrimary'])->name('admin.media.primary');

            // TMDB Search & Import
            Route::prefix('tmdb')->group(function () {
                Route::get('/search', [Admin\TmdbSearchController::class, 'search'])->name('admin.tmdb.search');
                Route::get('/movie/{tmdbId}', [Admin\TmdbSearchController::class, 'previewMovie'])->name('admin.tmdb.preview-movie');
                Route::get('/tv/{tmdbId}', [Admin\TmdbSearchController::class, 'previewTv'])->name('admin.tmdb.preview-tv');
                Route::post('/import/movie', [Admin\TmdbImportController::class, 'importMovie'])->name('admin.tmdb.import-movie');
                Route::post('/import/tv', [Admin\TmdbImportController::class, 'importTv'])->name('admin.tmdb.import-tv');
                Route::post('/import/bulk', [Admin\TmdbImportController::class, 'importBulk'])->name('admin.tmdb.import-bulk');
                Route::post('/sync-genres', [Admin\TmdbImportController::class, 'syncGenres'])->name('admin.tmdb.sync-genres');
            });
        });
    });
});
