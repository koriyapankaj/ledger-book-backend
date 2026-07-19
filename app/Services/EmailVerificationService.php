<?php

namespace App\Services;

use App\Mail\VerifyEmailCode;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public const CODE_TTL_MINUTES = 10;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    /**
     * Generate a fresh code, store it (hashed) and email it. Used on registration.
     */
    public function issueCode(string $email, ?string $name = null): void
    {
        $code = (string) random_int(100000, 999999);

        EmailVerification::updateOrCreate(
            ['email' => $email],
            [
                'code' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                'last_sent_at' => now(),
            ]
        );

        Mail::to($email)->send(new VerifyEmailCode($code, $name));
    }

    /**
     * Re-send a code, enforcing a cooldown between requests.
     *
     * @throws ValidationException
     */
    public function resend(string $email, ?string $name = null): void
    {
        $record = EmailVerification::where('email', $email)->first();

        if ($record && $record->last_sent_at) {
            $elapsed = (int) abs($record->last_sent_at->diffInSeconds(now()));

            if ($elapsed < self::RESEND_COOLDOWN_SECONDS) {
                $wait = self::RESEND_COOLDOWN_SECONDS - $elapsed;

                throw ValidationException::withMessages([
                    'code' => ["Please wait {$wait} seconds before requesting another code."],
                ]);
            }
        }

        $this->issueCode($email, $name);
    }

    /**
     * Validate a submitted code. Deletes the record on success.
     *
     * @throws ValidationException
     */
    public function verifyCode(string $email, string $code): void
    {
        $record = EmailVerification::where('email', $email)->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'code' => ['Please request a verification code first.'],
            ]);
        }

        if ($record->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['This code has expired. Please request a new one.'],
            ]);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => ['Too many incorrect attempts. Please request a new code.'],
            ]);
        }

        if (! Hash::check($code, $record->code)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'code' => ['The verification code is incorrect.'],
            ]);
        }

        $record->delete();
    }
}
