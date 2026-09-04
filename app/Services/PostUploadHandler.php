<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Models\Quality;
use App\Models\TvShow;
use App\Models\Watchlist;
use App\Notifications\NewEpisodeReleased;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostUploadHandler
{
    /**
     * Build the final storage path for a finished upload.
     */
    public function buildFinalPath(string $type, string $mediaType, int $mediableId, string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = Str::uuid()->toString().'.'.$extension;
        $subDir = match ($type) {
            'video' => 'videos',
            'subtitle' => 'subtitles',
            default => 'images',
        };

        $folderName = now()->format('Y/m');

        $mediable = match ($mediaType) {
            'movie', Movie::class => Movie::find($mediableId),
            'episode', Episode::class => Episode::with('season.tvShow')->find($mediableId),
            default => null,
        };

        if ($mediable instanceof Movie) {
            $folderName = $mediable->slug ?? Str::slug($mediable->title);
        } elseif ($mediable instanceof Episode && $mediable->season && $mediable->season->tvShow) {
            $tvShowSlug = $mediable->season->tvShow->slug ?? Str::slug($mediable->season->tvShow->name);
            $folderName = "{$tvShowSlug}/season-{$mediable->season->season_number}";
        }

        return "media/{$subDir}/{$folderName}/{$filename}";
    }

    /**
     * Create the Media record for a finished upload and run post-processing.
     *
     * @param  array{
     *     mediable_id: int,
     *     mediable_type: string,
     *     type: string,
     *     collection: string,
     *     disk: string,
     *     path: string,
     *     original_filename: string,
     *     mime_type: string,
     *     size: int,
     *     quality_id: int|null,
     *     metadata: array<string, mixed>|null,
     * }  $data
     */
    public function createAndProcess(array $data): Media
    {
        $width = null;
        $height = null;
        $duration = null;

        if ($data['type'] === 'image') {
            $absolutePath = Storage::disk($data['disk'])->path($data['path']);
            $sizes = @getimagesize($absolutePath);
            if ($sizes) {
                $width = $sizes[0];
                $height = $sizes[1];
            }
        } elseif ($data['type'] === 'video' && $data['quality_id']) {
            $quality = Quality::find($data['quality_id']);
            if ($quality) {
                $width = $quality->width;
                $height = $quality->height;
            }
        }

        $media = Media::unguarded(function () use ($data, $width, $height, $duration) {
            return Media::create([
                'mediable_id' => $data['mediable_id'],
                'mediable_type' => $data['mediable_type'],
                'quality_id' => $data['quality_id'],
                'type' => $data['type'],
                'collection' => $data['collection'],
                'disk' => $data['disk'],
                'path' => $data['path'],
                'original_filename' => $data['original_filename'],
                'mime_type' => $data['mime_type'],
                'size' => $data['size'],
                'width' => $width,
                'height' => $height,
                'duration' => $duration,
                'is_primary' => true,
                'metadata' => $data['metadata'],
            ]);
        });

        $this->notifyWatchers($media);

        return $media->load('quality');
    }

    private function notifyWatchers(Media $media): void
    {
        if ($media->type !== 'video') {
            return;
        }

        if ($media->mediable_type !== Episode::class) {
            return;
        }

        $episode = $media->mediable()->with('season.tvShow')->first();
        if (! $episode || $episode->media()->where('type', 'video')->count() !== 1) {
            return;
        }

        $tvShow = $episode->season->tvShow;
        if (! $tvShow) {
            return;
        }

        $watchlists = Watchlist::with('user')
            ->where('watchlistable_type', TvShow::class)
            ->where('watchlistable_id', $tvShow->id)
            ->get();

        foreach ($watchlists as $watchlist) {
            if ($watchlist->user) {
                $watchlist->user->notify(new NewEpisodeReleased(
                    $tvShow,
                    $episode->season->season_number,
                    $episode->episode_number,
                    $episode->name ?? "Episode {$episode->episode_number}"
                ));
            }
        }
    }
}
