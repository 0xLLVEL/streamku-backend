<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\TmdbClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TmdbSearchController extends Controller
{
    public function __construct(
        private TmdbClient $client,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:1'],
            'page' => ['integer', 'min:1'],
            'type' => ['string', 'in:movie,tv,multi'],
        ]);

        $query = $request->input('query');
        $page = $request->integer('page', 1);

        $results = match ($request->input('type', 'multi')) {
            'movie' => $this->client->searchMovies($query, $page),
            'tv' => $this->client->searchTv($query, $page),
            default => $this->client->searchMulti($query, $page),
        };

        return response()->json($results);
    }

    public function previewMovie(int $tmdbId): JsonResponse
    {
        return response()->json($this->client->getMovie($tmdbId));
    }

    public function previewTv(int $tmdbId): JsonResponse
    {
        return response()->json($this->client->getTvShow($tmdbId));
    }
}
