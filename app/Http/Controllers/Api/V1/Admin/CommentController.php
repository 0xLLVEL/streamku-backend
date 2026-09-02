<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with('user', 'commentable');

        if ($request->has('is_approved')) {
            $query->where('is_approved', filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('body', 'like', '%'.$request->input('search').'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->input('search').'%'));
            });
        }

        $allowedSorts = ['created_at', 'id'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $comments = $query->orderBy($sort, $direction)
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        return response()->json([
            'data' => $comments->getCollection()
                ->map(fn (Comment $comment) => $this->row($comment))
                ->values(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $comment->update(['is_approved' => true]);

        return $this->success($this->row($comment->load('user', 'commentable')));
    }

    public function hide(Comment $comment): JsonResponse
    {
        $comment->update(['is_approved' => false]);

        return $this->success($this->row($comment->load('user', 'commentable')));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return $this->success(null, 'Comment deleted.');
    }

    /** @return array<string, mixed> */
    private function row(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'user_name' => $comment->user?->name,
            'media_type' => match ($comment->commentable_type) {
                Movie::class => 'movie',
                TvShow::class => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $comment->commentable_id,
            'media_title' => $comment->commentable?->title ?? $comment->commentable?->name,
            'body' => $comment->body,
            'parent_id' => $comment->parent_id,
            'is_approved' => (bool) $comment->is_approved,
            'created_at' => $comment->created_at?->toIso8601String(),
        ];
    }
}
