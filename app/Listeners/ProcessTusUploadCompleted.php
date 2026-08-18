<?php

namespace App\Listeners;

use ArthurPatriot\Tus\Events\FileUploadFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Media;
use App\Models\Quality;
use App\Jobs\ProcessVideoDownscale;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProcessTusUploadCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FileUploadFinished $event): void
    {
        $tusFile = $event->tusFile;
        $metadata = $tusFile->metadata;

        // Ensure the file is not processed twice
        if (Media::where('metadata->tus_id', $tusFile->id)->exists()) {
            return;
        }

        $extension = $metadata['extension'] ?? pathinfo($metadata['filename'] ?? '', PATHINFO_EXTENSION);
        $filename = Str::uuid()->toString() . '.' . $extension;
        $type = $metadata['type'] ?? 'video';
        
        $subDir = match ($type) {
            'video' => 'videos',
            'subtitle' => 'subtitles',
            default => 'images',
        };
        
        $folderName = now()->format('Y/m');
        $mediableType = $metadata['mediable_type'] ?? null;
        $mediableId = $metadata['mediable_id'] ?? null;
        $qualityId = $metadata['quality_id'] ?? null;
        $collection = $metadata['collection'] ?? 'default';
        $disk = $type === 'video' ? 'local' : 'public';
        $originalFilename = $metadata['filename'] ?? $tusFile->id;
        $mimeType = $metadata['filetype'] ?? 'application/octet-stream';

        // Find mediable for folder naming
        if ($mediableType === 'movie') {
            $movie = \App\Models\Movie::find($mediableId);
            if ($movie) $folderName = $movie->slug ?? Str::slug($movie->title);
        } elseif ($mediableType === 'episode') {
            $episode = \App\Models\Episode::with('season.tvShow')->find($mediableId);
            if ($episode && $episode->season && $episode->season->tvShow) {
                $tvShowSlug = $episode->season->tvShow->slug ?? Str::slug($episode->season->tvShow->name);
                $folderName = "{$tvShowSlug}/season-{$episode->season->season_number}";
            }
        }

        $finalPath = "media/{$subDir}/{$folderName}/{$filename}";
        
        // Move file from TUS temp to final destination
        Storage::disk($disk)->put($finalPath, Storage::disk($tusFile->disk)->get($tusFile->path));
        Storage::disk($tusFile->disk)->delete($tusFile->path);

        $width = null;
        $height = null;
        $duration = null;
        
        if ($type === 'video' && $qualityId) {
            $quality = Quality::find($qualityId);
            if ($quality) {
                $width = $quality->width;
                $height = $quality->height;
            }
        }

        $media = Media::create([
            'mediable_id' => $mediableId,
            'mediable_type' => $mediableType === 'movie' ? \App\Models\Movie::class : \App\Models\Episode::class,
            'quality_id' => $qualityId,
            'type' => $type,
            'collection' => $collection,
            'disk' => $disk,
            'path' => $finalPath,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size' => $tusFile->metadata['size'] ?? 0,
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
            'is_primary' => true,
            'metadata' => array_merge($metadata, ['tus_id' => $tusFile->id]),
        ]);

        if ($media->type === 'video') {
            ProcessVideoDownscale::dispatch($media);

            if ($media->mediable_type === \App\Models\Episode::class) {
                $episode = $media->mediable()->with('season.tvShow')->first();
                if ($episode) {
                    $videoCount = $episode->media()->where('type', 'video')->count();
                    if ($videoCount === 1) {
                        $tvShow = $episode->season->tvShow;
                        $watchlists = \App\Models\Watchlist::with('user')
                            ->where('watchlistable_type', \App\Models\TvShow::class)
                            ->where('watchlistable_id', $tvShow->id)
                            ->get();

                        foreach ($watchlists as $watchlist) {
                            if ($watchlist->user) {
                                $watchlist->user->notify(new \App\Notifications\NewEpisodeReleased(
                                    $tvShow,
                                    $episode->season->season_number,
                                    $episode->episode_number,
                                    $episode->name ?? "Episode {$episode->episode_number}"
                                ));
                            }
                        }
                    }
                }
            }
        }
    }
}
