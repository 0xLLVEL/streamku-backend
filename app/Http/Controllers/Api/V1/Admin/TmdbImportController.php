<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\TmdbImportService;
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

        $movie = $this->importService->importMovie($request->integer('tmdb_id'));

        return $this->success($movie, 'Movie imported successfully.', 201);
    }

    public function importTv(Request $request): JsonResponse
    {
        $request->validate(['tmdb_id' => ['required', 'integer']]);

        $tvShow = $this->importService->importTvShow($request->integer('tmdb_id'));

        return $this->success($tvShow, 'TV show imported successfully.', 201);
    }

    public function importBulk(Request $request): JsonResponse
    {
        $request->validate([
            'tmdb_ids' => ['required', 'array', 'min:1', 'max:50'],
            'tmdb_ids.*' => ['integer'],
            'type' => ['required', 'string', 'in:movie,tv'],
        ]);

        $results = $this->importService->importBulk(
            $request->input('tmdb_ids'),
            $request->input('type')
        );

        return $this->success($results, $results->count().' items imported.', 201);
    }

    public function syncGenres(): JsonResponse
    {
        $this->importService->syncAllGenres();

        return $this->success(null, 'Genres synced from TMDB.');
    }
}
