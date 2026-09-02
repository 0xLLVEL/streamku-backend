<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class RegisterData extends Data
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public string $password_confirmation,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
