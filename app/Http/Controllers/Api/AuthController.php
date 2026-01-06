<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'currency' => $request->currency ?? 'INR',
            'timezone' => $request->timezone ?? 'Asia/Kolkata',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user (Hybrid: Cookie for Web, Token for Mobile)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        // 1. Check Credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 2. Check Active Status
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        // 3. Update last login
        $user->update(['last_login_at' => now()]);

        // =========================================================
        // SCENARIO A: Mobile App
        // Request sends explicit flag "is_mobile": true
        // =========================================================
        if (true || $request->boolean('is_mobile')) {
            $token = $user->createToken('android_device')->plainTextToken;

            return response()->json([
                'message' => 'Mobile Login successful',
                'user' => new UserResource($user),
                'token' => $token, // Return token ONLY for mobile
                'auth_type' => 'token'
            ]);
        }

        // =========================================================
        // SCENARIO B: Web App
        // Standard Browser Session (Secure Cookie)
        // =========================================================
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Web Login successful',
            'user' => new UserResource($user),
            'auth_type' => 'cookie'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Logout user
     * Handles both Token (Mobile) and Session (Web)
     */
    public function logout(Request $request): JsonResponse
    {
        // Check if the user is logged in via Token (Mobile)
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        } 
        // Otherwise, they are logged in via Cookie (Web)
        else {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     * Deletes all Tokens and invalidates current Session
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // 1. Delete ALL API tokens (Logs out all Android/iOS devices)
        $request->user()->tokens()->delete();

        // 2. If the current user is on Web, also kill the session
        // (We check !currentAccessToken because web users don't have one)
        if (! $request->user()->currentAccessToken()) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }
}