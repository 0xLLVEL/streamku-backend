<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportTmdbTitle;
use App\Services\TmdbImportService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TmdbImportController extends Controller
{
    public function __construct(
        private TmdbImportService $importService,
    ) {}

    public function importMovie(Request $request): JsonResponse
    {
        $request->validate(['tmdb_id' => ['required', 'integer']]);

        $language = $request->user()?->preferences['language'] ?? null;

        try {
            $movie = $this->importService->importMovie($request->integer('tmdb_id'), $language);
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'TMDB ID not found or is not a valid Movie.',
                ], 404);
            }
            throw $e;
        }

        return $this->success($movie, 'Movie imported successfully.', 201);
    }

    public function importTv(Request $request): JsonResponse
    {
        $request->validate(['tmdb_id' => ['required', 'integer']]);

        $language = $request->user()?->preferences['language'] ?? null;

        try {
            $tvShow = $this->importService->importTvShow($request->integer('tmdb_id'), $language);
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'TMDB ID not found or is not a valid TV Show.',
                ], 404);
            }
            throw $e;
        }

        return $this->success($tvShow, 'TV show imported successfully.', 201);
    }

    public function importBulk(Request $request): JsonResponse
    {
        $request->validate([
            'tmdb_ids' => ['required', 'array', 'min:1', 'max:50'],
            'tmdb_ids.*' => ['integer'],
            'type' => ['required', 'string', 'in:movie,tv'],
        ]);

        $language = $request->user()?->preferences['language'] ?? null;

        foreach ($request->input('tmdb_ids') as $tmdbId) {
            ImportTmdbTitle::dispatch($tmdbId, $request->input('type'), $language);
        }

        return $this->success(null, count($request->input('tmdb_ids')).' imports queued.', 202);
    }

    public function syncGenres(Request $request): JsonResponse
    {
        $language = $request->user()?->preferences['language'] ?? null;
        $this->importService->syncAllGenres($language);

        return $this->success(null, 'Genres synced from TMDB.');
    }
}
