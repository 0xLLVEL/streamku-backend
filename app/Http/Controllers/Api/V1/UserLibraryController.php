<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\FavoriteData;
use App\Data\WatchlistData;
use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class UserLibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lib = $this->library($request);

        $items = $request->user()->{$lib['relation']}()
            ->with($lib['morph'])
            ->latest()
            ->paginate(20);

        $items->setCollection($items->getCollection()->map(fn ($i) => $lib['data']::fromModel($i)));

        return $this->success($items->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $lib = $this->library($request);

        $validated = $request->validate([
            'media_id' => ['required', 'integer'],
            'media_type' => ['required', 'string', 'in:movie,tv_show'],
        ]);

        $morphType = MediaType::tryFrom($validated['media_type']);

        if (! $morphType) {
            return $this->error('Invalid media type.', 422);
        }

        $item = $request->user()->{$lib['relation']}()->firstOrCreate([
            $lib['id_column'] => $validated['media_id'],
            $lib['type_column'] => $morphType->modelClass(),
        ]);

        $item->load($lib['morph']);

        return $this->success($lib['data']::fromModel($item), null, 201);
    }

    public function destroy(Request $request, int $item): JsonResponse
    {
        $lib = $this->library($request);

        $record = $lib['model']::where('id', $item)->where('user_id', $request->user()->id)->firstOrFail();
        $record->delete();

        return $this->success(null, $request->route('library') === 'favorites' ? 'Removed from favorites.' : 'Removed from watchlist.');
    }

    /**
     * @return array{relation: string, morph: string, data: class-string<Data>, model: class-string<Model>, id_column: string, type_column: string}
     */
    private function library(Request $request): array
    {
        $favorites = $request->route('library') === 'favorites';

        return $favorites
            ? [
                'relation' => 'favorites',
                'morph' => 'favoritable',
                'data' => FavoriteData::class,
                'model' => Favorite::class,
                'id_column' => 'favoritable_id',
                'type_column' => 'favoritable_type',
            ]
            : [
                'relation' => 'watchlists',
                'morph' => 'watchlistable',
                'data' => WatchlistData::class,
                'model' => Watchlist::class,
                'id_column' => 'watchlistable_id',
                'type_column' => 'watchlistable_type',
            ];
    }
}
