<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'mediable_id', 'mediable_type', 'quality_id', 'type', 'collection',
    'disk', 'path', 'original_filename', 'mime_type', 'size',
    'duration', 'width', 'height', 'is_primary', 'metadata',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'metadata' => 'array',
        ];
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

    public function getUrlAttribute(): ?string
    {
        $disk = Storage::disk($this->disk);

        if (method_exists($disk, 'url')) {
            return $disk->url($this->path);
        }

        return null;
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }
}
