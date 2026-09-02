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
}
