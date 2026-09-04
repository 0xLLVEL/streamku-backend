<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WatchParty extends Model
{
    use HasUuids;

    protected $fillable = ['host_id', 'mediable_type', 'mediable_id'];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watch_party_users')->withTimestamps();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'host_id' => $this->host_id,
            'media_type' => match ($this->mediable_type) {
                'App\Models\Movie' => 'movie',
                'App\Models\Episode' => 'episode',
                default => 'unknown',
            },
            'media_id' => $this->mediable_id,
            'members' => $this->relationLoaded('members') ? $this->members->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ])->toArray() : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
