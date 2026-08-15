<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrowseController;
use App\Http\Controllers\Api\V1\GenreController;
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

    // ── Authenticated ───────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        // Browse
        Route::get('/browse', [BrowseController::class, 'index'])->name('browse');

        // Movies
        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');

        // TV Shows
        Route::get('/tv-shows', [TvShowController::class, 'index'])->name('tv-shows.index');
        Route::get('/tv-shows/{tvShow:slug}', [TvShowController::class, 'show'])->name('tv-shows.show');
        Route::get('/tv-shows/{tvShow:slug}/seasons/{season:season_number}', [TvShowController::class, 'season'])->name('tv-shows.seasons.show');
        Route::get('/tv-shows/{tvShow:slug}/seasons/{season:season_number}/episodes/{episode:episode_number}', [TvShowController::class, 'episode'])->name('tv-shows.seasons.episodes.show');

        // Genres
        Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
        Route::get('/genres/{genre:slug}', [GenreController::class, 'show'])->name('genres.show');

        // Watchlist
        Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
        Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
        Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');

        // Watch History
        Route::get('/history', [WatchHistoryController::class, 'index'])->name('history.index');
        Route::post('/history', [WatchHistoryController::class, 'store'])->name('history.store');
        Route::get('/history/continue-watching', [WatchHistoryController::class, 'continueWatching'])->name('history.continue');

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/reviews/{type}/{id}', [ReviewController::class, 'forTitle'])->name('reviews.for-title');

        // ── Admin ───────────────────────────────────────────
        Route::prefix('admin')->middleware(EnsureUserIsAdmin::class)->group(function () {

            // Admin Genres
            Route::apiResource('genres', Admin\GenreController::class)->names('admin.genres');

            // Admin Movies
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
            Route::apiResource('tv-shows.seasons', Admin\SeasonController::class)->names('admin.tv-shows.seasons')->parameters(['tv-shows' => 'tvShow']);

            // Admin Episodes
            Route::apiResource('tv-shows.seasons.episodes', Admin\EpisodeController::class)->names('admin.tv-shows.seasons.episodes')->parameters(['tv-shows' => 'tvShow']);

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
