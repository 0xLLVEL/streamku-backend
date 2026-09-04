<?php

namespace App\Models;

use Database\Factories\WatchlistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'watchlistable_id', 'watchlistable_type'])]
class Watchlist extends Model
{
    /** @use HasFactory<WatchlistFactory> */
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
    public function watchlistable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'media_type' => match ($this->watchlistable_type) {
                'App\Models\Movie' => 'movie',
                'App\Models\TvShow' => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $this->watchlistable_id,
            'media_details' => $this->relationLoaded('watchlistable') ? [
                'id' => $this->watchlistable->id,
                'title' => $this->watchlistable->title ?? $this->watchlistable->name,
                'poster_path' => $this->watchlistable->poster_path,
                'slug' => $this->watchlistable->slug,
            ] : null,
        ];
    }
}
