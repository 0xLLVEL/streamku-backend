<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\GenreData;
use App\Data\Requests\StoreGenreData;
use App\Data\Requests\UpdateGenreData;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Genre::orderBy('name')->paginate(50));
    }

    public function store(StoreGenreData $data): JsonResponse
    {
        $genre = Genre::create([
            'name' => $data->name,
            'slug' => Str::slug($data->name),
        ]);

        return response()->json(['data' => GenreData::from($genre)], 201);
    }

    public function show(Genre $genre): JsonResponse
    {
        return response()->json(['data' => GenreData::from($genre)]);
    }

    public function update(UpdateGenreData $data, Genre $genre): JsonResponse
    {
        $attributes = array_filter($data->toArray(), fn ($v) => $v !== null);

        if (isset($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        $genre->update($attributes);

        return response()->json(['data' => GenreData::from($genre->fresh())]);
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $genre->delete();

        return response()->json(['message' => 'Genre deleted.']);
    }
}
