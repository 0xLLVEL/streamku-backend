<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public bool $is_admin = false,
        public ?array $preferences = null,
        public ?string $created_at = null,
        public ?string $avatar = null,
        public ?string $nickname = null,
        // BC alias: some clients still read `name`
        public ?string $name = null,
    ) {
        $this->name ??= $this->username;
    }
}
