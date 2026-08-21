<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreFavoriteData;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function index(): JsonResponse
    {
        $items = request()->user()
            ->favorites()
            ->with('favoritable')
            ->latest()
            ->paginate(20);

        $items->setCollection($items->getCollection()->map(fn($f) => \App\Data\FavoriteData::fromModel($f)));
        return $this->success($items->toArray());
    }

    public function store(StoreFavoriteData $data): JsonResponse
    {
        $morphType = match ($data->favoritable_type) {
            'movie' => Movie::class,
            'tv_show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return $this->error('Invalid favoritable type.', 422);
        }

        $item = request()->user()->favorites()->firstOrCreate([
            'favoritable_id' => $data->favoritable_id,
            'favoritable_type' => $morphType,
        ]);

        $item->load('favoritable');

        return $this->success(\App\Data\FavoriteData::fromModel($item), null, 201);
    }

    public function destroy(Favorite $favorite): JsonResponse
    {
        if ($favorite->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $favorite->delete();

        return $this->success(null, 'Removed from favorites.');
    }
}
