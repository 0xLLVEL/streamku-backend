<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id', 'commentable_id', 'commentable_type',
    'parent_id', 'body', 'is_approved',
])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'media_type' => match ($this->commentable_type) {
                'App\Models\Movie' => 'movie',
                'App\Models\TvShow' => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $this->commentable_id,
            'body' => $this->body,
            'parent_id' => $this->parent_id,
            'user_name' => $this->relationLoaded('user') ? $this->user->username : null,
            'user_avatar' => $this->relationLoaded('user') ? $this->user->avatar : null,
            'user_nickname' => $this->relationLoaded('user') ? $this->user->nickname : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'is_approved' => $this->is_approved ? true : false,
            'replies' => $this->relationLoaded('replies') ? $this->replies->map(fn ($c) => [
                'id' => $c->id,
                'user_id' => $c->user_id,
                'media_type' => match ($c->commentable_type) {
                    'App\Models\Movie' => 'movie',
                    'App\Models\TvShow' => 'tv_show',
                    default => 'unknown',
                },
                'media_id' => $c->commentable_id,
                'body' => $c->body,
                'user_name' => $c->relationLoaded('user') ? $c->user->username : null,
                'user_avatar' => $c->relationLoaded('user') ? $c->user->avatar : null,
                'user_nickname' => $c->relationLoaded('user') ? $c->user->nickname : null,
                'created_at' => $c->created_at?->toIso8601String(),
                'is_approved' => $c->is_approved ? true : false,
            ])->all() : [],
        ];
    }
}
