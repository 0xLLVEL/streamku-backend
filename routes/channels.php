<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('watch-party.{partyId}', function ($user, $partyId) {
    $isMember = \App\Models\WatchPartyUser::where('watch_party_id', $partyId)
        ->where('user_id', $user->id)
        ->exists();
        
    if ($isMember) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    
    return false;
});