<?php

namespace App\Models;

use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tmdb_id', 'tv_show_id', 'season_number', 'name',
    'overview', 'poster_path', 'air_date', 'episode_count',
])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Season $season) {
            $season->episodes->each->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'air_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<TvShow, $this>
     */
    public function tvShow(): BelongsTo
    {
        return $this->belongsTo(TvShow::class);
    }

    /**
     * @return HasMany<Episode, $this>
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'tv_show_id' => $this->tv_show_id,
            'season_number' => $this->season_number,
            'name' => $this->name,
            'overview' => $this->overview,
            'poster_path' => $this->poster_path,
            'air_date' => $this->air_date?->format('Y-m-d'),
            'episode_count' => $this->episode_count,
            'episodes' => $this->relationLoaded('episodes') ? $this->episodes->map->toApiArray() : null,
        ];
    }
}
