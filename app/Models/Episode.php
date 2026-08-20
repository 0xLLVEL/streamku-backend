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
}
