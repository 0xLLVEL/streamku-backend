<?php

namespace App\Services;

use App\Data\UploadStatusData;
use App\Jobs\ProcessVideoDownscale;
use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Models\Quality;
use App\Models\Upload;
use App\Models\UploadChunk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkedUploadService
{
    private const DEFAULT_CHUNK_SIZE = 5 * 1024 * 1024; // 5MB

    /**
     * @param  array{
     *     filename: string,
     *     mime_type: string,
     *     total_size: int,
     *     mediable_id: int,
     *     mediable_type: string,
     *     type: string,
     *     quality_id: int|null,
     *     collection: string,
     * }  $data
     */
    public function initiate(array $data, int $userId): Upload
    {
        $chunkSize = self::DEFAULT_CHUNK_SIZE;
        $totalChunks = (int) ceil($data['total_size'] / $chunkSize);

        $morphType = match ($data['mediable_type']) {
            'movie' => Movie::class,
            'episode' => Episode::class,
            default => throw new \InvalidArgumentException('Invalid mediable type.'),
        };

        $disk = 'public';
        if ($data['type'] === 'video') {
            $disk = 'local';
        } elseif ($data['type'] === 'subtitle') {
            $disk = 'public'; // Store subtitles in public disk for web accessibility
        }

        return Upload::create([
            'upload_id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'filename' => $data['filename'],
            'mime_type' => $data['mime_type'],
            'total_size' => $data['total_size'],
            'chunk_size' => $chunkSize,
            'total_chunks' => $totalChunks,
            'received_chunks' => 0,
            'status' => 'pending',
            'disk' => $disk,
            'mediable_id' => $data['mediable_id'],
            'mediable_type' => $morphType,
            'quality_id' => $data['quality_id'] ?? null,
            'collection' => $data['collection'] ?? 'default',
            'type' => $data['type'],
            'metadata' => $data['metadata'] ?? null,
            'expires_at' => now()->addHours(24),
        ]);
    }

    public function storeChunk(Upload $upload, int $chunkNumber, UploadedFile $file): UploadChunk
    {
        if (! $upload->isUploadable()) {
            throw new \RuntimeException('Upload is not in an uploadable state.');
        }

        if ($upload->isExpired()) {
            throw new \RuntimeException('Upload session has expired.');
        }

        if ($chunkNumber >= $upload->total_chunks) {
            throw new \InvalidArgumentException("Invalid chunk number: {$chunkNumber}. Max: ".($upload->total_chunks - 1));
        }

        $chunkDir = "chunks/{$upload->upload_id}";
        $chunkPath = "{$chunkDir}/chunk_{$chunkNumber}";

        Storage::disk('local')->put($chunkPath, $file->getContent());

        $chunk = UploadChunk::updateOrCreate(
            [
                'upload_id' => $upload->id,
                'chunk_number' => $chunkNumber,
            ],
            [
                'size' => $file->getSize(),
                'path' => $chunkPath,
                'checksum' => md5_file($file->getRealPath()),
            ]
        );

        $receivedCount = $upload->chunks()->count();
        $upload->update([
            'received_chunks' => $receivedCount,
            'status' => 'uploading',
        ]);

        return $chunk;
    }

    public function complete(Upload $upload): Media
    {
        if ($upload->received_chunks < $upload->total_chunks) {
            $missing = $this->getMissingChunks($upload);

            throw new \RuntimeException('Missing chunks: '.implode(', ', $missing));
        }

        $upload->update(['status' => 'processing']);

        $extension = pathinfo($upload->filename, PATHINFO_EXTENSION);
        $filename = Str::uuid()->toString().'.'.$extension;
        $subDir = match ($upload->type) {
            'video' => 'videos',
            'subtitle' => 'subtitles',
            default => 'images',
        };
        $finalPath = "media/{$subDir}/".now()->format('Y/m')."/{$filename}";

        $this->mergeChunks($upload, $finalPath);

        $width = null;
        $height = null;
        $duration = null;
        $qualityId = $upload->quality_id;

        $disk = Storage::disk($upload->disk);
        $absolutePath = $disk->path($finalPath);

        if ($upload->type === 'image') {
            $sizes = @getimagesize($absolutePath);
            if ($sizes) {
                $width = $sizes[0];
                $height = $sizes[1];
            }
        } elseif ($upload->type === 'video' && $qualityId) {
            $quality = Quality::find($qualityId);
            if ($quality) {
                $width = $quality->width;
                $height = $quality->height;
            }
        }

        $media = Media::create([
            'mediable_id' => $upload->mediable_id,
            'mediable_type' => $upload->mediable_type,
            'quality_id' => $qualityId,
            'type' => $upload->type,
            'collection' => $upload->collection,
            'disk' => $upload->disk,
            'path' => $finalPath,
            'original_filename' => $upload->filename,
            'mime_type' => $upload->mime_type,
            'size' => $upload->total_size,
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
            'is_primary' => true,
            'metadata' => $upload->metadata,
        ]);

        $upload->update([
            'status' => 'completed',
            'path' => $finalPath,
        ]);

        $this->cleanupChunks($upload);

        if ($media->type === 'video') {
            ProcessVideoDownscale::dispatch($media);

            if ($media->mediable_type === \App\Models\Episode::class) {
                $episode = $media->mediable()->with('season.tvShow')->first();
                if ($episode) {
                    $videoCount = $episode->media()->where('type', 'video')->count();

                    // Only notify on the FIRST uploaded video for this episode
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

        return $media->load('quality');
    }

    public function cancel(Upload $upload): void
    {
        $this->cleanupChunks($upload);
        $upload->update(['status' => 'cancelled']);
    }

    public function getStatus(Upload $upload): UploadStatusData
    {
        return new UploadStatusData(
            upload_id: $upload->upload_id,
            status: $upload->status,
            received_chunks: $upload->received_chunks,
            total_chunks: $upload->total_chunks,
            progress_percent: $upload->progress_percent,
            missing_chunks: $this->getMissingChunks($upload),
        );
    }

    public function cleanupExpired(): int
    {
        $expired = Upload::where('expires_at', '<', now())
            ->whereIn('status', ['pending', 'uploading'])
            ->get();

        foreach ($expired as $upload) {
            $this->cleanupChunks($upload);
            $upload->update(['status' => 'failed']);
        }

        return $expired->count();
    }

    /**
     * @return int[]
     */
    private function getMissingChunks(Upload $upload): array
    {
        $received = $upload->chunks()->pluck('chunk_number')->toArray();
        $all = range(0, $upload->total_chunks - 1);

        return array_values(array_diff($all, $received));
    }

    private function mergeChunks(Upload $upload, string $finalPath): void
    {
        $disk = Storage::disk($upload->disk);
        $tempPath = sys_get_temp_dir().'/'.$upload->upload_id.'_merged';

        $output = fopen($tempPath, 'wb');

        if ($output === false) {
            throw new \RuntimeException('Could not create temporary merge file.');
        }

        $chunks = $upload->chunks()->orderBy('chunk_number')->get();

        foreach ($chunks as $chunk) {
            $chunkContent = Storage::disk('local')->get($chunk->path);

            if ($chunkContent === null) {
                fclose($output);
                throw new \RuntimeException("Chunk {$chunk->chunk_number} file not found.");
            }

            fwrite($output, $chunkContent);
        }

        fclose($output);

        $disk->put($finalPath, file_get_contents($tempPath));

        unlink($tempPath);
    }

    private function cleanupChunks(Upload $upload): void
    {
        $chunkDir = "chunks/{$upload->upload_id}";

        if (Storage::disk('local')->exists($chunkDir)) {
            Storage::disk('local')->deleteDirectory($chunkDir);
        }

        $upload->chunks()->delete();
    }
}
