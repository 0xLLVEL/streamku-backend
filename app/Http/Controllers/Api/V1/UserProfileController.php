<?php

namespace App\Http\Controllers\Api\V1;


use App\Data\WatchlistData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    public function show(User $user): JsonResponse
    {
        // For a public profile, we want to hide sensitive fields.
        // The User model already hides password and remember_token,
        // but we might want to also hide email.
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'created_at' => $user->created_at,
        ];

        // Fetch their favorites
        $favorites = $user->favorites()->with('favoritable')->latest()->get()
            ->map(fn ($f) => $f->toApiArray());

        // Fetch their watchlist
        $watchlist = $user->watchlists()->with('watchlistable')->latest()->get()
            ->map(fn ($w) => $w->toApiArray());

        return $this->success([
            'user' => $userData,
            'favorites' => $favorites,
            'watchlist' => $watchlist,
        ]);
    }
}
