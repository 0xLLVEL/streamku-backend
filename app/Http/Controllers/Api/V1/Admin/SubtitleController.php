<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Models\TvShow;
use App\Services\OpenSubtitlesClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubtitleController extends Controller
{
    public function __construct(
        private OpenSubtitlesClient $client,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tmdb_id' => 'required|integer|min:1',
            'languages' => 'sometimes|string|max:100',
        ]);

        $tmdbId = $validated['tmdb_id'];
        $languages = $validated['languages'] ?? 'en,id';

        $result = $this->client->search($tmdbId, $languages);

        return $this->success($result);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mediable_id' => 'required|integer|min:1',
            'mediable_type' => 'required|string|in:movie,episode,tv-show',
            'file_id' => 'required|integer|min:1',
            'file_name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10',
        ]);

        $link = $this->client->download($validated['file_id']);

        if (! $link) {
            return $this->error('Failed to get download link from OpenSubtitles. Check API key and quota.', 422);
        }

        $content = $this->client->fetchContent($link);

        if (! $content) {
            return $this->error('Failed to fetch subtitle content.', 422);
        }

        // Resolve mediable
        $mediaType = $validated['mediable_type'];
        $mediableId = $validated['mediable_id'];

        $mediable = match ($mediaType) {
            'movie' => Movie::find($mediableId),
            'episode' => Episode::find($mediableId),
            'tv-show' => TvShow::find($mediableId),
            default => null,
        };

        if (! $mediable) {
            return $this->error('Mediable not found.', 404);
        }

        $morphClass = match ($mediaType) {
            'movie' => Movie::class,
            'episode' => Episode::class,
            'tv-show' => TvShow::class,
        };

        $fileName = $validated['file_name'] ?? "subtitle_{$validated['file_id']}.srt";
        // Ensure .vtt extension for web compatibility; store as-is but serve with correct mime
        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'srt';
        $storedName = Str::uuid()->toString().'.'.$extension;

        // Build path consistent with PostUploadHandler
        $folderName = now()->format('Y/m');
        if ($mediable instanceof Movie) {
            $folderName = $mediable->slug ?? Str::slug($mediable->title);
        } elseif ($mediable instanceof Episode && $mediable->season && $mediable->season->tvShow) {
            $tvShowSlug = $mediable->season->tvShow->slug ?? Str::slug($mediable->season->tvShow->name);
            $folderName = "{$tvShowSlug}/season-{$mediable->season->season_number}";
        } elseif ($mediable instanceof TvShow) {
            $folderName = $mediable->slug ?? Str::slug($mediable->name);
        }

        $path = "media/subtitles/{$folderName}/{$storedName}";

        Storage::disk('public')->put($path, $content);

        $language = $validated['language'] ?? 'en';

        $media = Media::unguarded(fn () => Media::create([
            'mediable_id' => $mediable->id,
            'mediable_type' => $morphClass,
            'quality_id' => null,
            'type' => 'subtitle',
            'collection' => 'subtitles',
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $fileName,
            'mime_type' => $extension === 'vtt' ? 'text/vtt' : 'application/x-subrip',
            'size' => strlen($content),
            'is_primary' => false,
            'metadata' => ['language' => $language, 'source' => 'opensubtitles', 'file_id' => $validated['file_id']],
        ]));

        return $this->success($media->load('quality'), null, 201);
    }
}
