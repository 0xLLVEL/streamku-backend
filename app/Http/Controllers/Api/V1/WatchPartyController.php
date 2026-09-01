<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\WatchPartyData;
use App\Enums\MediaType;
use App\Events\WatchPartySynced;
use App\Http\Controllers\Controller;
use App\Models\WatchParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchPartyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mediable_type' => 'required|in:movie,episode',
            'mediable_id' => 'required|integer',
        ]);

        $morphType = MediaType::fromString($validated['mediable_type']);
        if (! $morphType) {
            return $this->error('Invalid mediable type.', 422);
        }

        $party = WatchParty::create([
            'host_id' => $request->user()->id,
            'mediable_type' => $morphType->modelClass(),
            'mediable_id' => $validated['mediable_id'],
        ]);

        $party->members()->attach($request->user()->id);

        return $this->success(WatchPartyData::fromModel($party));
    }

    public function show(WatchParty $watchParty): JsonResponse
    {
        $watchParty->load(['host', 'members', 'mediable']);

        return $this->success(WatchPartyData::fromModel($watchParty));
    }

    public function join(Request $request, WatchParty $watchParty): JsonResponse
    {
        $watchParty->members()->syncWithoutDetaching([$request->user()->id]);

        return $this->success(null, 'Joined successfully');
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

        return $this->success(null, 'Synced');
    }
}
