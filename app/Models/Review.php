<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id', 'reviewable_id', 'reviewable_type',
    'rating', 'body', 'is_approved',
])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
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
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'media_type' => match ($this->reviewable_type) {
                Movie::class => 'movie',
                TvShow::class => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $this->reviewable_id,
            'rating' => $this->rating,
            'body' => $this->body,
            'user_name' => $this->relationLoaded('user') ? $this->user->username : null,
            'user_avatar' => $this->relationLoaded('user') ? $this->user->avatar : null,
            'user_nickname' => $this->relationLoaded('user') ? $this->user->nickname : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'is_approved' => $this->is_approved ? true : false,
        ];
    }
}
