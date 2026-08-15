<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WatchParty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\WatchPartySynced;
use App\Models\Movie;
use App\Models\Episode;

class WatchPartyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mediable_type' => 'required|in:movie,episode',
            'mediable_id' => 'required|integer',
        ]);

        $type = $validated['mediable_type'] === 'movie' ? Movie::class : Episode::class;

        $party = WatchParty::create([
            'host_id' => $request->user()->id,
            'mediable_type' => $type,
            'mediable_id' => $validated['mediable_id'],
        ]);

        $party->members()->attach($request->user()->id);

        return response()->json(['data' => $party]);
    }

    public function show(WatchParty $watchParty): JsonResponse
    {
        $watchParty->load(['host', 'members', 'mediable']);
        
        return response()->json(['data' => $watchParty]);
    }

    public function join(Request $request, WatchParty $watchParty): JsonResponse
    {
        $watchParty->members()->syncWithoutDetaching([$request->user()->id]);

        return response()->json(['message' => 'Joined successfully']);
    }

    public function sync(Request $request, WatchParty $watchParty): JsonResponse
    {
        if ($request->user()->id !== $watchParty->host_id) {
            abort(403, 'Only the host can sync playback.');
        }

        $validated = $request->validate([
            'is_playing' => 'required|boolean',
            'current_time' => 'required|numeric',
        ]);

        broadcast(new WatchPartySynced($watchParty->id, $validated['is_playing'], $validated['current_time']))->toOthers();

        return response()->json(['message' => 'Synced']);
    }
}
