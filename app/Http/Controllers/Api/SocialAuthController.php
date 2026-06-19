<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $googleAuth)
    {
    }

    /**
     * Login / register with a Google ID token issued by Google Identity Services.
     */
    public function google(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        // Throws a 422 ValidationException (on the id_token field) if invalid.
        $profile = $this->googleAuth->verifyIdToken($validated['id_token']);

        $user = $this->findOrCreateUser($profile);

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('google_oauth')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Resolve the user for a verified Google profile: match by google_id, otherwise
     * link to an existing account with the same email, otherwise create a new user.
     *
     * @param  array{sub:string,email:string,name:?string,picture:?string}  $profile
     */
    private function findOrCreateUser(array $profile): User
    {
        $user = User::where('google_id', $profile['sub'])->first();

        if ($user) {
            return $user;
        }

        $user = User::where('email', $profile['email'])->first();

        if ($user) {
            // Link Google to the existing email/password account.
            $user->google_id = $profile['sub'];
            $user->avatar = $user->avatar ?: $profile['picture'];
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();

            return $user;
        }

        $user = User::create([
            'name' => $profile['name'] ?: $profile['email'],
            'email' => $profile['email'],
            'google_id' => $profile['sub'],
            'avatar' => $profile['picture'],
            'provider' => 'google',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
