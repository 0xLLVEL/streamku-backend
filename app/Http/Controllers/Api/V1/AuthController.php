<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Data\Auth\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterData $data): JsonResponse
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);
        $user->refresh();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => UserData::from($user),
            'token' => $token,
        ], null, 201);
    }

    public function login(LoginData $data): JsonResponse
    {
        $user = User::where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

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

    public function updateProfile(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'preferences' => ['sometimes', 'array'],
        ]);

        $user = $request->user();

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
