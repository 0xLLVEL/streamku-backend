<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Requests\StoreReviewData;
use App\Data\ReviewData;
use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = request()->user()
            ->reviews()
            ->with('reviewable')
            ->latest()
            ->paginate(20);

        $reviews->setCollection($reviews->getCollection()->map(fn ($r) => ReviewData::fromModel($r)));

        return $this->success($reviews->toArray());
    }

    public function store(StoreReviewData $data): JsonResponse
    {
        $morphType = MediaType::fromString($data->media_type);

        if (! $morphType) {
            return $this->error('Invalid media type.', 422);
        }

        $review = request()->user()->reviews()->updateOrCreate(
            [
                'reviewable_id' => $data->media_id,
                'reviewable_type' => $morphType->modelClass(),
            ],
            [
                'rating' => $data->rating,
                'body' => $data->body,
            ]
        );

        $review->load('user', 'reviewable');

        return $this->success(ReviewData::fromModel($review), null, 201);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $validated = $request->validate([
            'rating' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $review->update(array_filter($validated, fn ($v) => $v !== null));

        return $this->success(ReviewData::fromModel($review->fresh()->load('user', 'reviewable')));
    }

    public function destroy(Review $review): JsonResponse
    {
        if ($review->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $review->delete();

        return $this->success(null, 'Review deleted.');
    }

    public function forTitle(string $mediaType, int $id): JsonResponse
    {
        $morphType = MediaType::fromString($mediaType);

        if (! $morphType) {
            return $this->error('Invalid type.', 422);
        }

        $query = Review::with('user')
            ->where('reviewable_type', $morphType->modelClass())
            ->where('reviewable_id', $id);

        $user = \Auth::guard('sanctum')->user();

        $myReview = $user ? $query->clone()->where('user_id', $user->id)->first() : null;

        $approved = $query->where('is_approved', true)->latest()->get();

        return $this->success([
            'media_type' => $mediaType,
            'media_id' => $id,
            'avg_rating' => $approved->avg('rating'),
            'review_count' => $approved->count(),
            'my_review' => $myReview ? ReviewData::fromModel($myReview) : null,
            'reviews' => ReviewData::collect($approved),
        ]);
    }
}
