<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $is_admin,
        public ?string $created_at = null,
    ) {}
}
