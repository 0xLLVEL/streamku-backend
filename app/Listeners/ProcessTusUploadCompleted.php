<?php

namespace App\Listeners;

use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Services\PostUploadHandler;
use ArthurPatriot\Tus\Events\FileUploadFinished;
use Illuminate\Support\Facades\Storage;

class ProcessTusUploadCompleted
{
    public function __construct(
        private PostUploadHandler $postUpload,
    ) {}

    public function handle(FileUploadFinished $event): void
    {
        $tusFile = $event->tusFile;
        $metadata = $tusFile->metadata;

        // Ensure the file is not processed twice
        if (Media::where('metadata->tus_id', $tusFile->id)->exists()) {
            return;
        }

        $type = $metadata['type'] ?? 'video';
        $disk = $type === 'video' ? 'local' : 'public';
        $mediableType = $metadata['mediable_type'] ?? null;
        $mediableId = $metadata['mediable_id'] ?? null;

        if (! in_array($mediableType, ['movie', 'episode'], true) || ! $mediableId) {
            return;
        }

        $originalFilename = $metadata['filename'] ?? 'file.'.$metadata['extension'] ?? '';
        $finalPath = $this->postUpload->buildFinalPath(
            $type,
            $mediableType,
            $mediableId,
            $originalFilename,
        );

        // Move file from TUS temp to final destination
        Storage::disk($disk)->put($finalPath, Storage::disk($tusFile->disk)->get($tusFile->path));
        Storage::disk($tusFile->disk)->delete($tusFile->path);

        $this->postUpload->createAndProcess([
            'mediable_id' => $mediableId,
            'mediable_type' => $mediableType === 'movie' ? Movie::class : Episode::class,
            'type' => $type,
            'collection' => $metadata['collection'] ?? 'default',
            'disk' => $disk,
            'path' => $finalPath,
            'original_filename' => $originalFilename,
            'mime_type' => $metadata['filetype'] ?? 'application/octet-stream',
            'size' => $metadata['size'] ?? 0,
            'quality_id' => $metadata['quality_id'] ?? null,
            'metadata' => array_merge($metadata, ['tus_id' => $tusFile->id]),
        ]);
    }
}
