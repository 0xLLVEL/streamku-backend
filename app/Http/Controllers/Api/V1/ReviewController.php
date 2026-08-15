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

        $reviews->setCollection($reviews->getCollection()->map(fn($r) => \App\Data\ReviewData::fromModel($r)));
        return $this->success($reviews->toArray());
    }

    public function store(StoreReviewData $data): JsonResponse
    {
        $morphType = match ($data->reviewable_type) {
            'movie' => Movie::class,
            'tv_show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return $this->error('Invalid reviewable type.', 422);
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

        return $this->success(\App\Data\ReviewData::fromModel($review), null, 201);
    }

    public function update(UpdateReviewData $data, Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $review->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $this->success(\App\Data\ReviewData::fromModel($review->fresh()));
    }

    public function destroy(Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $review->delete();

        return $this->success(null, 'Review deleted.');
    }

    public function forTitle(string $type, int $id): JsonResponse
    {
        $morphType = match ($type) {
            'movie' => Movie::class,
            'tv_show', 'tv-show' => TvShow::class,
            default => null,
        };

        if (! $morphType) {
            return $this->error('Invalid type.', 422);
        }

        $reviews = Review::with('user')
            ->where('reviewable_type', $morphType)
            ->where('reviewable_id', $id)
            ->latest()
            ->paginate(20);

        $reviews->setCollection($reviews->getCollection()->map(fn($r) => \App\Data\ReviewData::fromModel($r)));
        return $this->success($reviews->toArray());
    }
}
