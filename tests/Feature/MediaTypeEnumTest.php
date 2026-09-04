<?php

use App\Enums\MediaType;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;
use App\Services\TmdbClient;

test('MediaType maps strings to model classes', function () {
    expect(MediaType::tryFrom('movie'))->not->toBeNull()
        ->and(MediaType::tryFrom('movie')->modelClass())->toBe(Movie::class)
        ->and(MediaType::tryFrom('tv_show')->modelClass())->toBe(TvShow::class)
        ->and(MediaType::tryFrom('episode')->modelClass())->toBe(Episode::class);
});

test('MediaType accepts only the canonical enum vocabulary', function (string $alias) {
    expect(MediaType::tryFrom($alias))->toBeNull();
})->with([
    'tv' => ['tv'],
    'tv-show' => ['tv-show'],
    'Movie' => ['Movie'],
    'TvShow' => ['TvShow'],
]);

test('MediaType rejects unknown strings', function () {
    expect(MediaType::tryFrom('book'))->toBeNull();
});

test('TmdbClient resolves from the container', function () {
    expect(app(TmdbClient::class))->toBeInstanceOf(TmdbClient::class);
});
