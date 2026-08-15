<?php

namespace App\Models;

use Database\Factories\UploadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'upload_id', 'user_id', 'filename', 'mime_type', 'total_size',
    'chunk_size', 'total_chunks', 'received_chunks', 'status',
    'disk', 'path', 'mediable_id', 'mediable_type',
    'quality_id', 'collection', 'type', 'metadata', 'expires_at',
])]
class Upload extends Model
{
    /** @use HasFactory<UploadFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'upload_id';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isUploadable(): bool
    {
        return in_array($this->status, ['pending', 'uploading']);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_chunks === 0) {
            return 0;
        }

        return round(($this->received_chunks / $this->total_chunks) * 100, 2);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<UploadChunk, $this>
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(UploadChunk::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Quality, $this>
     */
    public function quality(): BelongsTo
    {
        return $this->belongsTo(Quality::class);
    }
}
