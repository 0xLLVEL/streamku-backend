<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TMDB API Key
    |--------------------------------------------------------------------------
    |
    | Your TMDB API Read Access Token (v4 auth). This is used as a Bearer
    | token when making requests to the TMDB API v3 endpoints.
    |
    */

    'api_key' => env('TMDB_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | TMDB Base URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'),

    /*
    |--------------------------------------------------------------------------
    | TMDB Image Configuration
    |--------------------------------------------------------------------------
    */

    'image_base_url' => env('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p'),

    'poster_size' => env('TMDB_POSTER_SIZE', 'w500'),

    'backdrop_size' => env('TMDB_BACKDROP_SIZE', 'w1280'),

    'still_size' => env('TMDB_STILL_SIZE', 'w300'),

    'profile_size' => env('TMDB_PROFILE_SIZE', 'w185'),

];
