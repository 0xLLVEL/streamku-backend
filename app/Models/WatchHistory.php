<?php

namespace App\Models;

use Database\Factories\WatchHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id', 'watchable_id', 'watchable_type',
    'progress_seconds', 'duration_seconds', 'completed', 'last_watched_at',
    'ip_address', 'country',
])]
class WatchHistory extends Model
{
    /** @use HasFactory<WatchHistoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'last_watched_at' => 'datetime',
        ];
    }

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
    public function watchable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'media_type' => match ($this->watchable_type) {
                'App\Models\Movie' => 'movie',
                'App\Models\Episode' => 'episode',
                default => 'unknown',
            },
            'media_id' => $this->watchable_id,
            'progress_seconds' => $this->progress_seconds,
            'duration_seconds' => $this->duration_seconds,
            'completed' => $this->completed ? true : false,
            'last_watched_at' => $this->last_watched_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'item' => $this->relationLoaded('watchable') && $this->watchable ? [
                'id' => $this->watchable->id,
                'title' => $this->watchable_type === 'App\Models\Movie' ? $this->watchable->title : $this->watchable->name,
                'poster_path' => $this->watchable_type === 'App\Models\Movie' ? $this->watchable->poster_path : $this->watchable->still_path,
                'backdrop_path' => $this->watchable_type === 'App\Models\Movie' ? $this->watchable->backdrop_path : null,
                'slug' => $this->watchable_type === 'App\Models\Movie' ? $this->watchable->slug : ($this->watchable->season->tvShow->slug ?? ''),
                'season_number' => $this->watchable_type === 'App\Models\Movie' ? null : $this->watchable->season->season_number,
                'episode_number' => $this->watchable_type === 'App\Models\Movie' ? null : $this->watchable->episode_number,
                'tv_show_name' => $this->watchable_type === 'App\Models\Movie' ? null : ($this->watchable->season->tvShow->name ?? ''),
            ] : null,
        ];
    }
}
