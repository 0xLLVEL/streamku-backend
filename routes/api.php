<?php

use App\Http\Controllers\Api\V1\ActivityFeedController;
use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrowseController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FriendController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\MediaStreamController;
use App\Http\Controllers\Api\V1\MovieController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TvShowController;
use App\Http\Controllers\Api\V1\UserLibraryController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\WatchHistoryController;
use App\Http\Controllers\Api\V1\WatchPartyController;
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

    // ── Public browsing (works signed out) ─────────────────
    Route::get('/browse', [BrowseController::class, 'index'])->name('browse');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
    Route::get('/movies/{movie:slug}/recommendations', [MovieController::class, 'recommendations'])->name('movies.recommendations');

    Route::get('/tv-shows', [TvShowController::class, 'index'])->name('tv-shows.index');
    Route::get('/tv-shows/{tvShow:slug}', [TvShowController::class, 'show'])->name('tv-shows.show');
    Route::get('/tv-shows/{tvShow:slug}/recommendations', [TvShowController::class, 'recommendations'])->name('tv-shows.recommendations');
    Route::get('/tv-shows/{tvShow:slug}/seasons/{season_number}', [TvShowController::class, 'season'])->name('tv-shows.seasons.show');
    Route::get('/tv-shows/{tvShow:slug}/seasons/{season_number}/episodes/{episode_number}', [TvShowController::class, 'episode'])->name('tv-shows.seasons.episodes.show');

    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/{genre:slug}', [GenreController::class, 'show'])->name('genres.show');

    // Public read-only review & comment feeds (signed out can read)
    Route::get('/reviews/{media_type}/{id}', [ReviewController::class, 'forTitle'])->name('reviews.for-title');
    Route::get('/comments/{media_type}/{id}', [CommentController::class, 'forTitle'])->name('comments.for-title');

    Route::get('/users/{user}/profile', [UserProfileController::class, 'show'])->name('users.profile');

    // ── Authenticated ───────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::match(['put', 'post'], '/auth/me', [AuthController::class, 'updateProfile'])->name('auth.update');

        // Cast
        Route::get('/cast', [Admin\CastController::class, 'index'])->name('cast.index');

        // Watchlist
        Route::get('/watchlist', [UserLibraryController::class, 'index'])->defaults('library', 'watchlist')->name('watchlist.index');
        Route::post('/watchlist', [UserLibraryController::class, 'store'])->defaults('library', 'watchlist')->name('watchlist.store');
        Route::delete('/watchlist/{item}', [UserLibraryController::class, 'destroy'])->defaults('library', 'watchlist')->name('watchlist.destroy');

        // Favorites
        Route::get('/favorites', [UserLibraryController::class, 'index'])->defaults('library', 'favorites')->name('favorites.index');
        Route::post('/favorites', [UserLibraryController::class, 'store'])->defaults('library', 'favorites')->name('favorites.store');
        Route::delete('/favorites/{item}', [UserLibraryController::class, 'destroy'])->defaults('library', 'favorites')->name('favorites.destroy');

        // Watch History
        Route::get('/history', [WatchHistoryController::class, 'index'])->name('history.index');
        Route::post('/history', [WatchHistoryController::class, 'store'])->name('history.store');
        Route::get('/history/continue-watching', [WatchHistoryController::class, 'continueWatching'])->name('history.continue');

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Comments
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Watch Parties
        Route::post('/watch-parties', [WatchPartyController::class, 'store'])->name('watch-parties.store');
        Route::get('/watch-parties/{watchParty}', [WatchPartyController::class, 'show'])->name('watch-parties.show');
        Route::post('/watch-parties/{watchParty}/join', [WatchPartyController::class, 'join'])->name('watch-parties.join');
        Route::post('/watch-parties/{watchParty}/sync', [WatchPartyController::class, 'sync'])->name('watch-parties.sync');

        // Friends & Activity
        Route::prefix('friends')->group(function () {
            Route::get('/', [FriendController::class, 'index'])->name('friends.index');
            Route::get('/pending', [FriendController::class, 'pending'])->name('friends.pending');
            Route::post('/request', [FriendController::class, 'request'])->name('friends.request');
            Route::post('/{friend}/accept', [FriendController::class, 'accept'])->name('friends.accept');
            Route::delete('/{friend}', [FriendController::class, 'remove'])->name('friends.remove');
        });
        Route::get('/activity-feed', [ActivityFeedController::class, 'index'])->name('activity-feed');

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
            Route::get('/movies/{movie}/cast', [Admin\MediaCastController::class, 'index'])->name('admin.movies.cast.index');
            Route::post('/movies/{movie}/cast', [Admin\MediaCastController::class, 'store'])->name('admin.movies.cast.store');
            Route::put('/movies/{movie}/cast/{cast}', [Admin\MediaCastController::class, 'update'])->name('admin.movies.cast.update');
            Route::delete('/movies/{movie}/cast/{cast}', [Admin\MediaCastController::class, 'destroy'])->name('admin.movies.cast.destroy');
            Route::get('/movies/{movie}/videos', [Admin\VideoController::class, 'index'])->name('admin.movies.videos.index');
            Route::post('/movies/{movie}/videos', [Admin\VideoController::class, 'store'])->name('admin.movies.videos.store');
            Route::put('/movies/{movie}/videos/{video}', [Admin\VideoController::class, 'update'])->name('admin.movies.videos.update');
            Route::delete('/movies/{movie}/videos/{video}', [Admin\VideoController::class, 'destroy'])->name('admin.movies.videos.destroy');

            // Admin TV Shows
            Route::apiResource('tv-shows', Admin\TvShowController::class)->names('admin.tv-shows')->parameters(['tv-shows' => 'tvShow']);
            Route::patch('/tv-shows/{tvShow}/feature', [Admin\TvShowController::class, 'toggleFeatured'])->name('admin.tv-shows.feature');

            // Admin TV Show Cast & Videos
            Route::get('/tv-shows/{tvShow}/cast', [Admin\MediaCastController::class, 'index'])->name('admin.tv-shows.cast.index');
            Route::post('/tv-shows/{tvShow}/cast', [Admin\MediaCastController::class, 'store'])->name('admin.tv-shows.cast.store');
            Route::put('/tv-shows/{tvShow}/cast/{cast}', [Admin\MediaCastController::class, 'update'])->name('admin.tv-shows.cast.update');
            Route::delete('/tv-shows/{tvShow}/cast/{cast}', [Admin\MediaCastController::class, 'destroy'])->name('admin.tv-shows.cast.destroy');
            Route::get('/tv-shows/{tvShow}/videos', [Admin\VideoController::class, 'index'])->name('admin.tv-shows.videos.index');
            Route::post('/tv-shows/{tvShow}/videos', [Admin\VideoController::class, 'store'])->name('admin.tv-shows.videos.store');
            Route::put('/tv-shows/{tvShow}/videos/{video}', [Admin\VideoController::class, 'update'])->name('admin.tv-shows.videos.update');
            Route::delete('/tv-shows/{tvShow}/videos/{video}', [Admin\VideoController::class, 'destroy'])->name('admin.tv-shows.videos.destroy');

            // Admin Seasons
            Route::get('/seasons', [Admin\SeasonController::class, 'all'])->name('admin.seasons.all');
            Route::apiResource('tv-shows.seasons', Admin\SeasonController::class)->names('admin.tv-shows.seasons')->parameters(['tv-shows' => 'tvShow', 'seasons' => 'season_number']);

            // Admin Episodes
            Route::post('/tv-shows/{tvShow}/seasons/{season_number}/episodes/bulk-vidking', [Admin\EpisodeController::class, 'bulkVidking'])->name('admin.episodes.bulk-vidking');
            Route::post('/tv-shows/{tvShow}/seasons/{season_number}/episodes/bulk-embed', [Admin\EpisodeController::class, 'bulkVidking'])->name('admin.episodes.bulk-embed');
            Route::get('/episodes', [Admin\EpisodeController::class, 'all'])->name('admin.episodes.all');
            Route::apiResource('tv-shows.seasons.episodes', Admin\EpisodeController::class)->names('admin.tv-shows.seasons.episodes')->parameters(['tv-shows' => 'tvShow', 'seasons' => 'season_number', 'episodes' => 'episode_number']);
            Route::get('/tv-shows/{tvShow}/seasons/{season_number}/episodes/{episode_number}/videos', [Admin\VideoController::class, 'index'])->name('admin.episodes.videos.index');
            Route::post('/tv-shows/{tvShow}/seasons/{season_number}/episodes/{episode_number}/videos', [Admin\VideoController::class, 'store'])->name('admin.episodes.videos.store');
            Route::put('/tv-shows/{tvShow}/seasons/{season_number}/episodes/{episode_number}/videos/{video}', [Admin\VideoController::class, 'update'])->name('admin.episodes.videos.update');
            Route::delete('/tv-shows/{tvShow}/seasons/{season_number}/episodes/{episode_number}/videos/{video}', [Admin\VideoController::class, 'destroy'])->name('admin.episodes.videos.destroy');

            // Uploads (Chunked)
            Route::get('/uploads', [Admin\UploadController::class, 'index'])->name('admin.uploads.index');
            Route::post('/uploads/initiate', [Admin\UploadController::class, 'initiate'])->name('admin.uploads.initiate');
            Route::post('/uploads/{upload:upload_id}/chunks', [Admin\UploadController::class, 'chunk'])->name('admin.uploads.chunk');
            Route::get('/uploads/{upload:upload_id}/status', [Admin\UploadController::class, 'status'])->name('admin.uploads.status');
            Route::post('/uploads/{upload:upload_id}/complete', [Admin\UploadController::class, 'complete'])->name('admin.uploads.complete');
            Route::delete('/uploads/{upload:upload_id}', [Admin\UploadController::class, 'cancel'])->name('admin.uploads.cancel');

            // Qualities
            Route::apiResource('qualities', Admin\QualityController::class)->names('admin.qualities');

            // Admin Reviews moderation
            Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
            Route::post('/reviews/{review}/approve', [Admin\ReviewController::class, 'approve'])->name('admin.reviews.approve');
            Route::post('/reviews/{review}/hide', [Admin\ReviewController::class, 'hide'])->name('admin.reviews.hide');
            Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');

            // Admin Comments moderation
            Route::get('/comments', [Admin\CommentController::class, 'index'])->name('admin.comments.index');
            Route::post('/comments/{comment}/approve', [Admin\CommentController::class, 'approve'])->name('admin.comments.approve');
            Route::post('/comments/{comment}/hide', [Admin\CommentController::class, 'hide'])->name('admin.comments.hide');
            Route::delete('/comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('admin.comments.destroy');

            // Media Management
            Route::get('/movies/{movie}/media', [Admin\MediaController::class, 'movieMedia'])->name('admin.movies.media');
            Route::get('/tv-shows/{tvShow}/seasons/{season}/episodes/{episode}/media', [Admin\MediaController::class, 'episodeMedia'])->name('admin.episodes.media');
            Route::delete('/media/{media}', [Admin\MediaController::class, 'destroy'])->name('admin.media.destroy');
            Route::patch('/media/{media}/primary', [Admin\MediaController::class, 'setPrimary'])->name('admin.media.primary');

            // Subtitles (OpenSubtitles + manual)
            Route::get('/subtitles/search', [Admin\SubtitleController::class, 'search'])->name('admin.subtitles.search');
            Route::post('/subtitles/import', [Admin\SubtitleController::class, 'import'])->name('admin.subtitles.import');

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
