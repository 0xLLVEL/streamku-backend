<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Genre::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        $allowedSorts = ['name', 'slug', 'id'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $direction);

        $perPage = $request->input('per_page', 20);

        return response()->json($query->paginate($perPage)->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:genres,name'],
        ]);

        $genre = Genre::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json(['data' => $genre->toApiArray()], 201);
    }

    public function show(Genre $genre): JsonResponse
    {
        return response()->json(['data' => $genre->toApiArray()]);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $attributes = array_filter($validated, fn ($v) => $v !== null);

        if (isset($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        $genre->update($attributes);

        return response()->json(['data' => $genre->fresh()->toApiArray()]);
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $genre->delete();

        return $this->success(null, 'Genre deleted.');
    }
}
