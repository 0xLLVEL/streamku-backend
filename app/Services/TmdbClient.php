<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TmdbClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('tmdb.base_url'))
            ->withToken(config('tmdb.api_key'))
            ->acceptJson()
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    public function getMovie(int $id, array $params = []): array
    {
        $imageLangs = 'en,null';
        if (isset($params['language'])) {
            $imageLangs .= ',' . $params['language'];
        }

        return $this->http
            ->get("/movie/{$id}", array_merge([
                'append_to_response' => 'credits,videos,images',
                'include_image_language' => $imageLangs
            ], $params))
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTvShow(int $id, array $params = []): array
    {
        $imageLangs = 'en,null';
        if (isset($params['language'])) {
            $imageLangs .= ',' . $params['language'];
        }

        return $this->http
            ->get("/tv/{$id}", array_merge([
                'append_to_response' => 'credits,videos,images',
                'include_image_language' => $imageLangs
            ], $params))
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTvSeason(int $tvId, int $seasonNumber, array $params = []): array
    {
        return $this->http
            ->get("/tv/{$tvId}/season/{$seasonNumber}", $params)
            ->json();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMovies(string $query, int $page = 1, array $params = []): array
    {
        return $this->http
            ->get('/search/movie', array_merge(['query' => $query, 'page' => $page], $params))
            ->json();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchTv(string $query, int $page = 1, array $params = []): array
    {
        return $this->http
            ->get('/search/tv', array_merge(['query' => $query, 'page' => $page], $params))
            ->json();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMulti(string $query, int $page = 1, array $params = []): array
    {
        return $this->http
            ->get('/search/multi', array_merge(['query' => $query, 'page' => $page], $params))
            ->json();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function discoverMovies(array $filters = []): array
    {
        return $this->http
            ->get('/discover/movie', $filters)
            ->json();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function discoverTv(array $filters = []): array
    {
        return $this->http
            ->get('/discover/tv', $filters)
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function trending(string $type = 'all', string $window = 'day'): array
    {
        return $this->http
            ->get("/trending/{$type}/{$window}")
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getGenres(string $type = 'movie', array $params = []): array
    {
        return $this->http
            ->get("/genre/{$type}/list", $params)
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPopularMovies(int $page = 1): array
    {
        return $this->http
            ->get('/movie/popular', ['page' => $page])
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPopularTv(int $page = 1): array
    {
        return $this->http
            ->get('/tv/popular', ['page' => $page])
            ->json();
    }
}
