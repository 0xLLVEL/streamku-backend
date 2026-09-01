<?php

use App\Contracts\TmdbPort;
use App\Enums\MediaType;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;

test('MediaType maps strings to model classes', function () {
    expect(MediaType::fromString('movie'))->not->toBeNull()
        ->and(MediaType::fromString('movie')->modelClass())->toBe(Movie::class)
        ->and(MediaType::fromString('tv_show')->modelClass())->toBe(TvShow::class)
        ->and(MediaType::fromString('tv')->modelClass())->toBe(TvShow::class)
        ->and(MediaType::fromString('episode')->modelClass())->toBe(Episode::class);
});

test('MediaType rejects unknown strings', function () {
    expect(MediaType::fromString('book'))->toBeNull();
});

test('TmdbPort is bound to TmdbClient in the container', function () {
    expect(app(TmdbPort::class))->toBeInstanceOf(TmdbPort::class);
});
