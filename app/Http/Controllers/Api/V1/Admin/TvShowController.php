<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreTvShowData;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class TvShowController extends MediaAdminController
{
    protected function modelClass(): string
    {
        return TvShow::class;
    }

    protected function titleColumn(): string
    {
        return 'name';
    }

    protected function dateColumn(): string
    {
        return 'first_air_date';
    }

    protected function allowedSorts(): array
    {
        return ['name', 'first_air_date', 'views', 'created_at'];
    }

    protected function updateRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'first_air_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'episode_run_time' => ['nullable', 'integer', 'min:1'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }

    protected function showRelations(): array
    {
        return ['genres', 'cast', 'videos', 'seasons.episodes.videos', 'seasons.episodes.season'];
    }

    protected function updateRelations(): array
    {
        return ['genres', 'cast', 'videos', 'seasons'];
    }

    public function store(StoreTvShowData $data): JsonResponse
    {
        return $this->storeMedia($data->except('genre_ids')->toArray(), $data->genre_ids);
    }
}
