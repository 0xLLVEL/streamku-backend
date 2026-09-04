<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\CommentData;
use App\Data\Requests\StoreCommentData;
use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function forTitle(string $mediaType, int $id): JsonResponse
    {
        $morphType = MediaType::tryFrom($mediaType);

        if (! $morphType) {
            return $this->error('Invalid type.', 422);
        }

        $base = fn () => Comment::where('commentable_type', $morphType->modelClass())
            ->where('commentable_id', $id);

        $comments = $base()
            ->with('user', 'replies.user')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->latest()
            ->get()
            ->map(function (Comment $comment) {
                $comment->setRelation(
                    'replies',
                    $comment->replies->where('is_approved', true)->values()
                );

                return $comment;
            });

        return $this->success([
            'media_type' => $mediaType,
            'media_id' => $id,
            'total' => $base()->where('is_approved', true)->count(),
            'comments' => CommentData::collect($comments),
        ]);
    }

    public function store(StoreCommentData $data): JsonResponse
    {
        $morphType = MediaType::tryFrom($data->media_type);

        if (! $morphType) {
            return $this->error('Invalid media type.', 422);
        }

        $comment = request()->user()->comments()->create([
            'commentable_id' => $data->media_id,
            'commentable_type' => $morphType->modelClass(),
            'parent_id' => $data->parent_id,
            'body' => $data->body,
        ]);

        $comment->load('user');

        return $this->success(CommentData::fromModel($comment), null, 201);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment->update($validated);

        return $this->success(CommentData::fromModel($comment->load('user')));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== request()->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted.');
    }
}
