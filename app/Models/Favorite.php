<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'favoritable_id',
        'favoritable_type',
    ];

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
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'media_type' => match ($this->favoritable_type) {
                'App\Models\Movie' => 'movie',
                'App\Models\TvShow' => 'tv_show',
                default => 'unknown',
            },
            'media_id' => $this->favoritable_id,
            'media_details' => $this->relationLoaded('favoritable') ? [
                'id' => $this->favoritable->id,
                'title' => $this->favoritable->title ?? $this->favoritable->name,
                'poster_path' => $this->favoritable->poster_path,
                'slug' => $this->favoritable->slug,
            ] : null,
        ];
    }
}
