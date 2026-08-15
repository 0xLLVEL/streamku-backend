<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamController extends Controller
{
    public function movieMedia(Movie $movie): JsonResponse
    {
        $media = $movie->media()->with('quality')->get()
            ->map(fn($m) => \App\Data\MediaData::from($m))
            ->groupBy('collection');

        return $this->success($media);
    }

    public function episodeMedia(TvShow $tvShow, Season $season, Episode $episode): JsonResponse
    {
        $media = $episode->media()->with('quality')->get()
            ->map(fn($m) => \App\Data\MediaData::from($m))
            ->groupBy('collection');

        return $this->success($media);
    }

    public function stream(Media $media): StreamedResponse
    {
        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            abort(404, 'Media file not found.');
        }

        $fullPath = $disk->path($media->path);
        $fileSize = $disk->size($media->path);
        $mimeType = $media->mime_type;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $fileSize,
        ];

        // Handle range requests for video seeking
        $request = request();
        $start = 0;
        $end = $fileSize - 1;
        $statusCode = 200;

        if ($request->header('Range')) {
            $range = $request->header('Range');

            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int) $matches[1];
                $end = ! empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;

                $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
                $headers['Content-Length'] = $end - $start + 1;
                $statusCode = 206;
            }
        }

        return response()->stream(function () use ($fullPath, $start, $end) {
            $stream = fopen($fullPath, 'rb');

            if ($stream === false) {
                return;
            }

            fseek($stream, $start);

            $remaining = $end - $start + 1;
            $bufferSize = 8192;

            while ($remaining > 0 && ! feof($stream)) {
                $readSize = min($bufferSize, $remaining);
                $data = fread($stream, $readSize);

                if ($data === false) {
                    break;
                }

                echo $data;
                $remaining -= strlen($data);
                flush();
            }

            fclose($stream);
        }, $statusCode, $headers);
    }
}
