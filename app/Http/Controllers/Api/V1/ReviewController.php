<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreReviewData;
use App\Data\Requests\UpdateReviewData;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = request()->user()
            ->reviews()
            ->with('reviewable')
            ->latest()
            ->paginate(20);

        return response()->json($reviews);
    }

    public function store(StoreReviewData $data): JsonResponse
    {
        $morphType = match ($data->reviewable_type) {
            'movie' => Movie::class,
            'tv_show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return response()->json(['message' => 'Invalid reviewable type.'], 422);
        }

        $review = request()->user()->reviews()->updateOrCreate(
            [
                'reviewable_id' => $data->reviewable_id,
                'reviewable_type' => $morphType,
            ],
            [
                'rating' => $data->rating,
                'body' => $data->body,
            ]
        );

        $review->load('reviewable');

        return response()->json(['data' => $review], 201);
    }

    public function update(UpdateReviewData $data, Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $review->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return response()->json(['data' => $review->fresh()]);
    }

    public function destroy(Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }

    public function forTitle(string $type, int $id): JsonResponse
    {
        $morphType = match ($type) {
            'movie' => Movie::class,
            'tv_show', 'tv-show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return response()->json(['message' => 'Invalid type.'], 422);
        }

        $reviews = Review::with('user')
            ->where('reviewable_type', $morphType)
            ->where('reviewable_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json($reviews);
    }
}
