<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleAuthService
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    private const VALID_ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * Verify a Google ID token (the "credential" returned by Google Identity Services)
     * and return its normalized claims.
     *
     * Verification is done through Google's official tokeninfo endpoint, which checks
     * the signature and expiry. We additionally enforce the audience, issuer and that
     * the email is verified. This keeps the implementation dependency-free; swap the
     * HTTP call for local JWKS verification (google/apiclient) if you need higher scale.
     *
     * @return array{sub:string,email:string,name:?string,picture:?string}
     *
     * @throws ValidationException
     */
    public function verifyIdToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            $this->fail('Google login is not configured on the server.');
        }

        try {
            $response = Http::timeout(10)->get(self::TOKENINFO_URL, [
                'id_token' => $idToken,
            ]);
        } catch (\Throwable) {
            $this->fail('Unable to verify Google token. Please try again.');
        }

        if (! $response->ok()) {
            $this->fail('Invalid or expired Google token.');
        }

        $payload = $response->json();

        if (! is_array($payload) || empty($payload['sub']) || empty($payload['email'])) {
            $this->fail('Invalid Google token.');
        }

        // Audience must match our own client ID (prevents tokens minted for other apps).
        if (($payload['aud'] ?? null) !== $clientId) {
            $this->fail('This Google token was not issued for this application.');
        }

        // Issuer must be Google.
        if (! in_array($payload['iss'] ?? '', self::VALID_ISSUERS, true)) {
            $this->fail('Invalid Google token issuer.');
        }

        // Token must not be expired (tokeninfo enforces this too; double-check).
        if (isset($payload['exp']) && (int) $payload['exp'] < time()) {
            $this->fail('Google token has expired. Please try again.');
        }

        // Email must be verified by Google.
        $emailVerified = $payload['email_verified'] ?? false;
        if ($emailVerified !== true && $emailVerified !== 'true') {
            $this->fail('Your Google email address is not verified.');
        }

        return [
            'sub' => (string) $payload['sub'],
            'email' => (string) $payload['email'],
            'name' => $payload['name'] ?? null,
            'picture' => $payload['picture'] ?? null,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'id_token' => [$message],
        ]);
    }
}
