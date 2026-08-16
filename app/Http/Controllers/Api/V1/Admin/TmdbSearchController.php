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
            'include_adult' => ['boolean'],
        ]);

        $query = $request->input('query');
        $page = $request->integer('page', 1);
        
        $userPrefAdult = $request->user()?->preferences['include_adult'] ?? null;
        $fallback = $userPrefAdult !== null ? (bool) $userPrefAdult : false;

        $params = [];
        if ($request->boolean('include_adult', $fallback)) {
            $params['include_adult'] = 'true';
        }

        $userPrefLanguage = $request->user()?->preferences['language'] ?? null;
        if ($userPrefLanguage) {
            $params['language'] = $userPrefLanguage;
        }

        $results = match ($request->input('type', 'multi')) {
            'movie' => $this->client->searchMovies($query, $page, $params),
            'tv' => $this->client->searchTv($query, $page, $params),
            default => $this->client->searchMulti($query, $page, $params),
        };

        return $this->success($results);
    }

    public function previewMovie(int $tmdbId): JsonResponse
    {
        return $this->success($this->client->getMovie($tmdbId));
    }

    public function previewTv(int $tmdbId): JsonResponse
    {
        return $this->success($this->client->getTvShow($tmdbId));
    }
}
