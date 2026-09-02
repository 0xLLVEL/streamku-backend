<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $friends = $request->user()->friends()->select('users.id', 'users.name')->get();

        return $this->success($friends);
    }

    public function pending(Request $request): JsonResponse
    {
        $requests = $request->user()->pendingFriendRequests()->select('users.id', 'users.name')->get();

        return $this->success($requests);
    }

    public function request(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        if ($request->user()->id === $validated['friend_id']) {
            return $this->error('You cannot add yourself as a friend.', 422);
        }

        $exists = DB::table('friends')
            ->where('user_id', $request->user()->id)
            ->where('friend_id', $validated['friend_id'])
            ->exists();

        if ($exists) {
            return $this->error('Friend request already sent or you are already friends.', 422);
        }

        // Also check if they already sent us a request
        $incomingExists = DB::table('friends')
            ->where('user_id', $validated['friend_id'])
            ->where('friend_id', $request->user()->id)
            ->first();

        if ($incomingExists) {
            if ($incomingExists->status === 'pending') {
                return $this->error('They have already sent you a request. Please accept it instead.', 422);
            }

            return $this->error('You are already friends.', 422);
        }

        DB::table('friends')->insert([
            'user_id' => $request->user()->id,
            'friend_id' => $validated['friend_id'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->success(null, 'Friend request sent.', 201);
    }

    public function accept(Request $request, User $friend): JsonResponse
    {
        $pending = DB::table('friends')
            ->where('user_id', $friend->id)
            ->where('friend_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if (! $pending) {
            return $this->error('No pending friend request found from this user.', 404);
        }

        DB::transaction(function () use ($request, $friend) {
            // Update their request to accepted
            DB::table('friends')
                ->where('user_id', $friend->id)
                ->where('friend_id', $request->user()->id)
                ->update([
                    'status' => 'accepted',
                    'updated_at' => now(),
                ]);

            // Create our reciprocal accepted relationship
            DB::table('friends')->insert([
                'user_id' => $request->user()->id,
                'friend_id' => $friend->id,
                'status' => 'accepted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $this->success(null, 'Friend request accepted.');
    }

    public function remove(Request $request, User $friend): JsonResponse
    {
        DB::table('friends')
            ->where(function ($query) use ($request, $friend) {
                $query->where('user_id', $request->user()->id)
                    ->where('friend_id', $friend->id);
            })
            ->orWhere(function ($query) use ($request, $friend) {
                $query->where('user_id', $friend->id)
                    ->where('friend_id', $request->user()->id);
            })
            ->delete();

        return $this->success(null, 'Friend removed or request cancelled.');
    }
}
