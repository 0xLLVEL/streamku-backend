<?php

namespace App\Models;

use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'tmdb_id', 'season_id', 'episode_number', 'name',
    'overview', 'still_path', 'air_date', 'runtime',
    'vote_average', 'vote_count',
])]
class Episode extends Model
{
    /** @use HasFactory<EpisodeFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Episode $episode) {
            $episode->media()->delete();
            $episode->videos()->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'air_date' => 'date',
            'vote_average' => 'decimal:1',
        ];
    }

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * @return MorphMany<WatchHistory, $this>
     */
    public function watchHistories(): MorphMany
    {
        return $this->morphMany(WatchHistory::class, 'watchable');
    }

    /**
     * @return MorphMany<Video, $this>
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'season_id' => $this->season_id,
            'season_number' => $this->season?->season_number,
            'episode_number' => $this->episode_number,
            'name' => $this->name,
            'overview' => $this->overview,
            'still_path' => $this->still_path,
            'air_date' => $this->air_date?->format('Y-m-d'),
            'runtime' => $this->runtime,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'videos' => $this->relationLoaded('videos') ? $this->videos->map->toApiArray() : null,
            'history' => $this->relationLoaded('watchHistories') && $this->watchHistories->first() ? [
                'id' => $this->watchHistories->first()->id,
                'user_id' => $this->watchHistories->first()->user_id,
                'media_type' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? 'movie' : ($this->watchHistories->first()->watchable_type === 'App\Models\Episode' ? 'episode' : 'unknown'),
                'media_id' => $this->watchHistories->first()->watchable_id,
                'progress_seconds' => $this->watchHistories->first()->progress_seconds,
                'duration_seconds' => $this->watchHistories->first()->duration_seconds,
                'completed' => $this->watchHistories->first()->completed,
                'last_watched_at' => $this->watchHistories->first()->last_watched_at?->toIso8601String(),
                'created_at' => $this->watchHistories->first()->created_at?->toIso8601String(),
                'item' => $this->watchHistories->first()->watchable ? [
                    'id' => $this->watchHistories->first()->watchable->id,
                    'title' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? $this->watchHistories->first()->watchable->title : $this->watchHistories->first()->watchable->name,
                    'poster_path' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? $this->watchHistories->first()->watchable->poster_path : $this->watchHistories->first()->watchable->still_path,
                    'backdrop_path' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? $this->watchHistories->first()->watchable->backdrop_path : null,
                    'slug' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? $this->watchHistories->first()->watchable->slug : ($this->watchHistories->first()->watchable->season->tvShow?->slug ?? ''),
                    'season_number' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? null : $this->watchHistories->first()->watchable->season->season_number,
                    'episode_number' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? null : $this->watchHistories->first()->watchable->episode_number,
                    'tv_show_name' => $this->watchHistories->first()->watchable_type === 'App\Models\Movie' ? null : ($this->watchHistories->first()->watchable->season->tvShow?->name ?? ''),
                ] : null,
            ] : null,
        ];
    }
}
