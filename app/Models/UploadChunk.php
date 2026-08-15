<?php

namespace App\Models;

use Database\Factories\UploadChunkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['upload_id', 'chunk_number', 'size', 'path', 'checksum'])]
class UploadChunk extends Model
{
    /** @use HasFactory<UploadChunkFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Upload, $this>
     */
    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }
}
