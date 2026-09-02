<?php

namespace App\Data;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\WatchParty;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class WatchPartyData extends Data
{
    public function __construct(
        public string $id,
        public int $host_id,
        public string $media_type,
        public int $media_id,
        public ?array $members = null,
        public ?string $created_at = null,
    ) {}

    public static function fromModel(WatchParty $party): self
    {
        $members = null;
        if ($party->relationLoaded('members')) {
            $members = $party->members->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ])->toArray();
        }

        return new self(
            id: $party->id,
            host_id: $party->host_id,
            media_type: match ($party->mediable_type) {
                Movie::class => 'movie',
                Episode::class => 'episode',
                default => 'unknown',
            },
            media_id: $party->mediable_id,
            members: $members,
            created_at: $party->created_at?->toIso8601String(),
        );
    }
}
