<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class MediaAdminController extends Controller
{
    /**
     * The Eloquent model class this controller manages.
     *
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    abstract protected function titleColumn(): string;

    abstract protected function dateColumn(): string;

    /**
     * @return string[]
     */
    abstract protected function allowedSorts(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    abstract protected function updateRules(): array;

    /**
     * @return string[]
     */
    abstract protected function showRelations(): array;

    /**
     * @return string[]
     */
    abstract protected function updateRelations(): array;

    public function index(Request $request): JsonResponse
    {
        $model = $this->modelClass();
        $query = $model::with('genres');

        if ($request->filled('search')) {
            $query->where($this->titleColumn(), 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $request->input('genre')));
        }

        if ($request->filled('year')) {
            $query->whereYear($this->dateColumn(), $request->input('year'));
        }

        if ($request->filled('language')) {
            $query->where('original_language', $request->input('language'));
        }

        $sort = in_array($request->input('sort'), $this->allowedSorts()) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        return $this->success($query->paginate($request->input('per_page', 20))->toArray());
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $genreIds
     */
    protected function storeMedia(array $attributes, array $genreIds): JsonResponse
    {
        $model = $this->modelClass();

        $media = $model::create([
            ...$attributes,
            'slug' => Str::slug($attributes[$this->titleColumn()].'-'.Str::random(5)),
        ]);

        if (! empty($genreIds)) {
            $media->genres()->sync($genreIds);
        }

        $media->load('genres');

        return $this->success($media, null, 201);
    }

    public function show(): JsonResponse
    {
        $media = $this->boundModel();
        $media->load($this->showRelations());

        return $this->success($media);
    }

    public function update(Request $request): JsonResponse
    {
        $media = $this->boundModel();
        $validated = $request->validate($this->updateRules());

        $attributes = array_filter(Arr::except($validated, 'genre_ids'), fn ($v) => $v !== null);

        if (isset($attributes[$this->titleColumn()])) {
            $attributes['slug'] = Str::slug($attributes[$this->titleColumn()].'-'.($media->tmdb_id ?? $media->id));
        }

        $media->update($attributes);

        if (($validated['genre_ids'] ?? null) !== null) {
            $media->genres()->sync($validated['genre_ids']);
        }

        return $this->success($media->fresh($this->updateRelations()));
    }

    public function destroy(): JsonResponse
    {
        $this->boundModel()->delete();

        return $this->success(null, 'Media deleted.');
    }

    public function toggleFeatured(): JsonResponse
    {
        $media = $this->boundModel();
        $media->update(['is_featured' => ! $media->is_featured]);

        return $this->success($media->fresh(), $media->is_featured ? 'Featured.' : 'Unfeatured.');
    }

    /**
     * Resolve the route-bound model (e.g. {movie} or {tvShow}) for this controller.
     */
    protected function boundModel(): Model
    {
        $param = Str::camel(class_basename($this->modelClass()));
        $id = request()->route($param);

        return $this->modelClass()::findOrFail($id);
    }
}
