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
}
