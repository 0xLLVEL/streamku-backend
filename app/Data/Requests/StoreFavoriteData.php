<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\In;

class StoreFavoriteData extends Data
{
    public function __construct(
        public int $favoritable_id,
        #[In(['movie', 'tv_show'])]
        public string $favoritable_type,
    ) {}
}
