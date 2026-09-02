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
        if ($this->media->type !== 'video' || ! $this->media->height) {
            return;
        }

        // We want all qualities up to the original video's height
        $qualities = Quality::where('height', '<=', $this->media->height)
            ->orderByDesc('height')
            ->get();

        if ($qualities->isEmpty()) {
            return;
        }

        $hlsDir = "media/videos/hls/{$this->media->id}";
        $playlistPath = "{$hlsDir}/master.m3u8";

        Log::info("Starting HLS export for Media ID {$this->media->id}.");

        try {
            $export = FFMpeg::fromDisk($this->media->disk)
                ->open($this->media->path)
                ->exportForHLS()
                ->setSegmentLength(10)
                ->setKeyFrameInterval(48);

            foreach ($qualities as $quality) {
                $format = (new X264)->setKiloBitrate($quality->bitrate ?? 2000);

                $export->addFormat($format, function ($media) use ($quality) {
                    $media->scale($quality->width, $quality->height);
                });
            }

            // Save HLS to public disk so it can be served dynamically
            $export->toDisk('public')->save($playlistPath);

            // Delete original MP4
            if (Storage::disk($this->media->disk)->exists($this->media->path)) {
                Storage::disk($this->media->disk)->delete($this->media->path);
            }

            // Update Media Model
            $this->media->update([
                'disk' => 'public',
                'path' => $playlistPath,
                'mime_type' => 'application/vnd.apple.mpegurl',
            ]);

            Log::info("HLS Export complete for Media ID {$this->media->id} saved to {$playlistPath}.");

        } catch (\Throwable $e) {
            Log::error("Failed to export HLS for Media ID {$this->media->id}: ".$e->getMessage());

            // Clean up partial HLS files
            if (Storage::disk('public')->exists($hlsDir)) {
                Storage::disk('public')->deleteDirectory($hlsDir);
            }
        }
    }
}
