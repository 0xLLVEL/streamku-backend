<?php

namespace App\Enums;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;

enum MediaType: string
{
    case Movie = 'movie';
    case TvShow = 'tv_show';
    case Episode = 'episode';

    public function modelClass(): string
    {
        return match ($this) {
            self::Movie => Movie::class,
            self::TvShow => TvShow::class,
            self::Episode => Episode::class,
        };
    }

    public static function fromString(string $value): ?self
    {
        return match ($value) {
            'movie', 'Movie' => self::Movie,
            'tv', 'tv_show', 'tv-show', 'TvShow' => self::TvShow,
            'episode' => self::Episode,
            default => null,
        };
    }
}
