<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class FavoriteData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $media_type,
        public int $media_id,
        public ?array $media_details = null,
        public ?string $created_at = null,
    ) {}

    public static function fromModel(\App\Models\Favorite $favorite): self
    {
        $mediaDetails = null;
        if ($favorite->relationLoaded('favoritable') && $favorite->favoritable) {
            $mediaDetails = [
                'id' => $favorite->favoritable->id,
                'title' => $favorite->favoritable->title ?? $favorite->favoritable->name,
                'poster_path' => $favorite->favoritable->poster_path,
                'slug' => $favorite->favoritable->slug,
            ];
        }

        return new self(
            id: $favorite->id,
            user_id: $favorite->user_id,
            media_type: match ($favorite->favoritable_type) {
                \App\Models\Movie::class => 'movie',
                \App\Models\TvShow::class => 'tv_show',
                default => 'unknown',
            },
            media_id: $favorite->favoritable_id,
            media_details: $mediaDetails,
            created_at: $favorite->created_at?->toIso8601String(),
        );
    }
}
