<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\Quality;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProcessVideoDownscale implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // Allow 1 hour for transcoding

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Media $media
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Must be a video and have a defined quality height to downscale from
        if ($this->media->type !== 'video' || ! $this->media->height) {
            return;
        }

        // Find all qualities with a height lower than the original video
        $lowerQualities = Quality::where('height', '<', $this->media->height)
            ->orderByDesc('height')
            ->get();

        if ($lowerQualities->isEmpty()) {
            return;
        }

        $extension = pathinfo($this->media->original_filename, PATHINFO_EXTENSION);
        $baseDir = dirname($this->media->path); // e.g. media/videos/2026/08

        foreach ($lowerQualities as $quality) {
            $newFilename = Str::uuid()->toString().'.'.$extension;
            $newPath = $baseDir.'/'.$newFilename;

            Log::info("Starting downscale for Media ID {$this->media->id} to {$quality->label} ({$quality->height}p).");

            try {
                $format = new X264;
                if ($quality->bitrate) {
                    $format->setKiloBitrate($quality->bitrate);
                }

                FFMpeg::fromDisk($this->media->disk)
                    ->open($this->media->path)
                    ->export()
                    ->toDisk($this->media->disk)
                    ->inFormat($format)
                    ->resize($quality->width, $quality->height)
                    ->save($newPath);

                // Re-open transcoded file to get exact final stats (optional, but good for accuracy)
                $newMediaFile = FFMpeg::fromDisk($this->media->disk)->open($newPath);
                $newStream = $newMediaFile->getVideoStream();

                Media::create([
                    'mediable_id' => $this->media->mediable_id,
                    'mediable_type' => $this->media->mediable_type,
                    'quality_id' => $quality->id,
                    'type' => 'video',
                    'collection' => $this->media->collection,
                    'disk' => $this->media->disk,
                    'path' => $newPath,
                    'original_filename' => $quality->name.'_'.$this->media->original_filename,
                    'mime_type' => $this->media->mime_type,
                    'size' => Storage::disk($this->media->disk)->size($newPath),
                    'duration' => $this->media->duration,
                    'width' => $newStream?->getDimensions()?->getWidth() ?? $quality->width,
                    'height' => $newStream?->getDimensions()?->getHeight() ?? $quality->height,
                    'is_primary' => false,
                ]);

                Log::info("Downscale complete: {$quality->label} ({$quality->height}p) saved to {$newPath}.");
            } catch (\Throwable $e) {
                Log::error("Failed to downscale Media ID {$this->media->id} to {$quality->label}: ".$e->getMessage());
                // Clean up partial file if it exists
                if (Storage::disk($this->media->disk)->exists($newPath)) {
                    Storage::disk($this->media->disk)->delete($newPath);
                }
            }
        }
    }
}
