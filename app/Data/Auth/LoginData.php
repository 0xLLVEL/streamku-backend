<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class LoginData extends Data
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
