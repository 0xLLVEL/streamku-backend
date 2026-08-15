<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class StoreCastData extends Data
{
    public function __construct(
        public string $name,
        public ?string $character = null,
        public ?string $profile_path = null,
        public int $order = 0,
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'character' => ['nullable', 'string', 'max:255'],
            'profile_path' => ['nullable', 'string', 'max:500'],
            'order' => ['integer', 'min:0'],
        ];
    }
}
