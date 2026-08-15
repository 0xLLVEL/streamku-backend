<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreVideoData extends Data
{
    public function __construct(
        public string $key,
        public string $site,
        public string $type,
        public string $name,
        public bool $official = false,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'site' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'official' => ['boolean'],
        ];
    }
}
