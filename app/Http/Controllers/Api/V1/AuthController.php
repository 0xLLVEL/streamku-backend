<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Auth\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $ip = request()->ip();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'ip_address' => $ip,
            'country' => null, // ponytail: location lookup removed
        ]);
        $user->refresh();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => UserData::from($user),
            'token' => $token,
        ], null, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $ip = request()->ip();

        $user->update([
            'ip_address' => $ip,
            'country' => null,
        ]);

        return $this->success([
            'user' => UserData::from($user),
            'token' => $token,
        ]);
    }

    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(): JsonResponse
    {
        return $this->success([
            'user' => UserData::from(request()->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username,'.$user->id],
            'nickname' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'current_password' => ['sometimes', 'nullable', 'string'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'preferences' => ['sometimes', 'array'],
            'preferences.include_adult' => ['sometimes', 'boolean'],
            'preferences.dark_mode' => ['sometimes', 'boolean'],
            'preferences.language' => ['sometimes', 'string', 'max:10'],
        ]);

        // Handle password change
        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect.'],
                ]);
            }
            $user->password = Hash::make($validated['password']);
            unset($validated['password'], $validated['current_password'], $validated['password_confirmation']);
        } else {
            unset($validated['password'], $validated['current_password'], $validated['password_confirmation']);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = '/storage/'.$path;
        } elseif (array_key_exists('avatar', $validated) && $validated['avatar'] === null) {
            $validated['avatar'] = null;
        }

        if (isset($validated['preferences'])) {
            $user->preferences = array_merge((array) $user->preferences, $validated['preferences']);
            unset($validated['preferences']);
        }

        $user->fill($validated);
        $user->save();

        return $this->success([
            'user' => UserData::from($user),
        ], 'Profile updated successfully.');
    }
}
