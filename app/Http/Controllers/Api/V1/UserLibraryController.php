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
            $lib['id_col'] => ['required', 'integer'],
            $lib['type_col'] => ['required', 'string', 'in:movie,tv_show'],
        ]);

        $morphType = MediaType::fromString($validated[$lib['type_col']]);

        if (! $morphType) {
            return $this->error('Invalid type.', 422);
        }

        $item = $request->user()->{$lib['relation']}()->firstOrCreate([
            $lib['id_col'] => $validated[$lib['id_col']],
            $lib['type_col'] => $morphType->modelClass(),
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
     * @return array{relation: string, morph: string, data: class-string<Data>, model: class-string<Model>, id_col: string, type_col: string}
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
                'id_col' => 'favoritable_id',
                'type_col' => 'favoritable_type',
            ]
            : [
                'relation' => 'watchlists',
                'morph' => 'watchlistable',
                'data' => WatchlistData::class,
                'model' => Watchlist::class,
                'id_col' => 'watchlistable_id',
                'type_col' => 'watchlistable_type',
            ];
    }
}
