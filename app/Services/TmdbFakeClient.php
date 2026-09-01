<?php

namespace App\Services;

use App\Contracts\TmdbPort;

class TmdbFakeClient implements TmdbPort
{
    /**
     * @param  array<string, array<string, mixed>>  $fixtures  keyed by API path
     */
    public function __construct(
        private array $fixtures = [],
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getMovie(int $id, array $params = []): array
    {
        return $this->fixture("/movie/{$id}");
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getTvShow(int $id, array $params = []): array
    {
        return $this->fixture("/tv/{$id}");
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getTvSeason(int $tvId, int $seasonNumber, array $params = []): array
    {
        return $this->fixture("/tv/{$tvId}/season/{$seasonNumber}");
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMovies(string $query, int $page = 1, array $params = []): array
    {
        return $this->fixture('/search/movie');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchTv(string $query, int $page = 1, array $params = []): array
    {
        return $this->fixture('/search/tv');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMulti(string $query, int $page = 1, array $params = []): array
    {
        return $this->fixture('/search/multi');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getGenres(string $type = 'movie', array $params = []): array
    {
        return $this->fixture("/genre/{$type}/list");
    }

    /**
     * @return array<string, mixed>
     */
    public function getPopularMovies(int $page = 1): array
    {
        return $this->fixture('/movie/popular');
    }

    /**
     * @return array<string, mixed>
     */
    public function getPopularTv(int $page = 1): array
    {
        return $this->fixture('/tv/popular');
    }

    /**
     * @return array<string, mixed>
     */
    private function fixture(string $path): array
    {
        return $this->fixtures[$path] ?? [];
    }
}
