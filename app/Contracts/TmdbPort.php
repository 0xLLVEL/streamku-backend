<?php

namespace App\Contracts;

interface TmdbPort
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getMovie(int $id, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getTvShow(int $id, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getTvSeason(int $tvId, int $seasonNumber, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMovies(string $query, int $page = 1, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchTv(string $query, int $page = 1, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchMulti(string $query, int $page = 1, array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getGenres(string $type = 'movie', array $params = []): array;

    /**
     * @return array<string, mixed>
     */
    public function getPopularMovies(int $page = 1): array;

    /**
     * @return array<string, mixed>
     */
    public function getPopularTv(int $page = 1): array;
}
