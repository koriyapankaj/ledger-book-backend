<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly EmailVerificationService $verification)
    {
    }

    /**
     * Verify the submitted 6-digit code and, on success, log the user in.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found for this email address.',
            ], 404);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        if (! $user->email_verified_at) {
            // Throws a 422 ValidationException (on the "code" field) when invalid.
            $this->verification->verifyCode($user->email, $validated['code']);

            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Re-send a verification code to an unverified account.
     */
    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found for this email address.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'This email is already verified. You can sign in.',
                'already_verified' => true,
            ]);
        }

        // Throws a 422 ValidationException (on "code") when the cooldown is active.
        $this->verification->resend($user->email, $user->name);

        return response()->json([
            'message' => 'A new verification code has been sent to your email.',
        ]);
    }
}
