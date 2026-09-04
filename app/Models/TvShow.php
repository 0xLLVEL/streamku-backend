<?php

namespace App\Models;

use Database\Factories\TvShowFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'tmdb_id', 'name', 'slug', 'overview', 'tagline', 'trailer_url',
    'poster_path', 'backdrop_path', 'first_air_date', 'last_air_date',
    'number_of_seasons', 'number_of_episodes', 'episode_run_time',
    'vote_average', 'vote_count', 'popularity',
    'original_language', 'status', 'type', 'is_featured', 'images',
])]
class TvShow extends Model
{
    /** @use HasFactory<TvShowFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (TvShow $tvShow) {
            $tvShow->cast()->delete();
            $tvShow->videos()->delete();
            $tvShow->reviews()->delete();
            $tvShow->watchlists()->delete();
            $tvShow->media()->delete();
            $tvShow->genres()->detach();
            $tvShow->seasons()->each(fn ($season) => $season->delete());
        });

        static::saved(fn () => Cache::forget('browse_rows'));
        static::deleted(fn () => Cache::forget('browse_rows'));
    }

    /**
     * @return Builder<TvShow>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @return Builder<TvShow>
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('popularity');
    }

    /**
     * @return Builder<TvShow>
     */
    public function scopeTopRated(Builder $query): Builder
    {
        return $query->where('vote_count', '>=', 10)->orderByDesc('vote_average');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_air_date' => 'date',
            'last_air_date' => 'date',
            'is_featured' => 'boolean',
            'vote_average' => 'decimal:1',
            'popularity' => 'decimal:3',
            'images' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<Genre, $this>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /**
     * @return HasMany<Season, $this>
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    /**
     * @return MorphMany<Cast, $this>
     */
    public function cast(): MorphMany
    {
        return $this->morphMany(Cast::class, 'castable');
    }

    /**
     * @return MorphMany<Video, $this>
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    /**
     * @return MorphMany<Review, $this>
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * @return MorphMany<Watchlist, $this>
     */
    public function watchlists(): MorphMany
    {
        return $this->morphMany(Watchlist::class, 'watchlistable');
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'overview' => $this->overview,
            'tagline' => $this->tagline,
            'poster_path' => $this->poster_path,
            'backdrop_path' => $this->backdrop_path,
            'first_air_date' => $this->first_air_date?->format('Y-m-d'),
            'last_air_date' => $this->last_air_date?->format('Y-m-d'),
            'number_of_seasons' => $this->number_of_seasons,
            'number_of_episodes' => $this->number_of_episodes,
            'episode_run_time' => $this->episode_run_time,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'popularity' => $this->popularity,
            'original_language' => $this->original_language,
            'status' => $this->status,
            'type' => $this->type,
            'is_featured' => $this->is_featured,
            'genres' => $this->genres->map->toApiArray()->all(),
            'cast' => $this->cast->filter()->map->toApiArray()->all(),
            'videos' => $this->videos->filter()->map->toApiArray()->all(),
            'seasons' => $this->seasons->sortBy('season_number')->map->toApiArray()->all(),
            'images' => $this->images,
        ];
    }
}
