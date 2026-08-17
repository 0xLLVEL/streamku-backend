<?php

namespace App\Models;

use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'tmdb_id', 'title', 'slug', 'overview', 'tagline',
    'poster_path', 'backdrop_path', 'release_date', 'runtime',
    'vote_average', 'vote_count', 'popularity',
    'original_language', 'status', 'is_featured', 'images',
])]
class Movie extends Model
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Movie $movie) {
            $movie->cast()->delete();
            $movie->videos()->delete();
            $movie->reviews()->delete();
            $movie->watchlists()->delete();
            $movie->media()->delete();
            $movie->genres()->detach();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
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
}
