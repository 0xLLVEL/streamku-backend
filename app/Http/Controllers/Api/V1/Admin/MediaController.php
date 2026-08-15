<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Media;
use App\Models\Movie;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function movieMedia(Movie $movie): JsonResponse
    {
        $media = $movie->media()->with('quality')->get();

        return $this->success($media);
    }

    public function episodeMedia(TvShow $tvShow, Season $season, Episode $episode): JsonResponse
    {
        $media = $episode->media()->with('quality')->get();

        return $this->success($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        $media->deleteFile();
        $media->delete();

        return $this->success(null, 'Media deleted.');
    }

    public function setPrimary(Media $media): JsonResponse
    {
        // Unset other primaries of same type/collection on the same model
        Media::where('mediable_id', $media->mediable_id)
            ->where('mediable_type', $media->mediable_type)
            ->where('type', $media->type)
            ->where('collection', $media->collection)
            ->where('id', '!=', $media->id)
            ->update(['is_primary' => false]);

        $media->update(['is_primary' => true]);

        return response()->json(['data' => $media->fresh('quality'), 'message' => 'Set as primary.']);
    }
}
