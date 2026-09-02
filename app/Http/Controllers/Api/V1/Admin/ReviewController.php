<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with('user', 'reviewable');

        if ($request->has('is_approved')) {
            $query->where('is_approved', filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('body', 'like', '%'.$request->input('search').'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->input('search').'%'));
            });
        }

        $allowedSorts = ['created_at', 'rating', 'id'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $reviews = $query->orderBy($sort, $direction)
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        return response()->json([
            'data' => $reviews->getCollection()
                ->map(fn (Review $review) => $this->row($review))
                ->values(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function approve(Review $review): JsonResponse
    {
        $review->update(['is_approved' => true]);

        return $this->success($this->row($review->load('user', 'reviewable')));
    }

    public function hide(Review $review): JsonResponse
    {
        $review->update(['is_approved' => false]);

        return $this->success($this->row($review->load('user', 'reviewable')));
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return $this->success(null, 'Review deleted.');
    }

    /** @return array<string, mixed> */
    private function row(Review $review): array
    {
        return [
            'id' => $review->id,
            'user_id' => $review->user_id,
            'user_name' => $review->user?->name,
            'media_type' => match ($review->reviewable_type) {
                Movie::class => 'movie',
                TvShow::class => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $review->reviewable_id,
            'media_title' => $review->reviewable?->title ?? $review->reviewable?->name,
            'rating' => $review->rating,
            'body' => $review->body,
            'is_approved' => (bool) $review->is_approved,
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }
}
