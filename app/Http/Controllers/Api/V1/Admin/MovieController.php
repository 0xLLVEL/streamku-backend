<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\StoreMovieData;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;

class MovieController extends MediaAdminController
{
    protected function modelClass(): string
    {
        return Movie::class;
    }

    protected function titleColumn(): string
    {
        return 'title';
    }

    protected function dateColumn(): string
    {
        return 'release_date';
    }

    protected function allowedSorts(): array
    {
        return ['title', 'release_date', 'views', 'created_at'];
    }

    protected function updateRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'release_date' => ['nullable', 'date'],
            'runtime' => ['nullable', 'integer', 'min:1'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'original_language' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }

    protected function showRelations(): array
    {
        return ['genres', 'cast', 'videos', 'media.quality'];
    }

    protected function updateRelations(): array
    {
        return $this->showRelations();
    }

    public function store(StoreMovieData $data): JsonResponse
    {
        return $this->storeMedia($data->except('genre_ids')->toArray(), $data->genre_ids);
    }
}
