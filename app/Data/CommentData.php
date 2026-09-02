<?php

namespace App\Data;

use App\Models\Comment;
use App\Models\Movie;
use App\Models\TvShow;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class CommentData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $media_type,
        public int $media_id,
        public string $body,
        public ?int $parent_id = null,
        public ?string $user_name = null,
        public ?string $user_avatar = null,
        public ?string $user_nickname = null,
        public ?string $created_at = null,
        public bool $is_approved = true,
        /** @var list<CommentData> */
        public array $replies = [],
    ) {}

    public static function fromModel(Comment $comment): self
    {
        return new self(
            id: $comment->id,
            user_id: $comment->user_id,
            media_type: match ($comment->commentable_type) {
                Movie::class => 'movie',
                TvShow::class => 'tv_show',
                default => 'unknown',
            },
            media_id: $comment->commentable_id,
            body: $comment->body,
            parent_id: $comment->parent_id,
            user_name: $comment->relationLoaded('user') ? $comment->user->username : null,
            user_avatar: $comment->relationLoaded('user') ? $comment->user->avatar : null,
            user_nickname: $comment->relationLoaded('user') ? $comment->user->nickname : null,
            created_at: $comment->created_at?->toIso8601String(),
            is_approved: (bool) $comment->is_approved,
            replies: $comment->relationLoaded('replies')
                ? $comment->replies->map(fn (Comment $reply) => self::fromModel($reply))->all()
                : [],
        );
    }
}
