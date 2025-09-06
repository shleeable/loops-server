<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Verify Cloudflare Turnstile token
     */
    public function verifyTurnstile(string $token, ?string $remoteIp = null): bool
    {
        $secretKey = config('services.turnstile.secret_key');

        if (! $secretKey) {
            Log::error('Turnstile secret key not configured');

            return false;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $remoteIp ?? request()->ip(),
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success'])) {
                return $result['success'] === true;
            }

            if (config('logging.dev_log')) {
                Log::warning('Turnstile verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'response' => $result,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('Turnstile verification exception', [
                    'message' => $e->getMessage(),
                    'token' => substr($token, 0, 10).'...',
                ]);
            }

            return false;
        }
    }

    /**
     * Verify hCaptcha token
     */
    public function verifyHCaptcha(string $token, ?string $remoteIp = null): bool
    {
        $secretKey = config('services.hcaptcha.secret_key');

        if (! $secretKey) {
            Log::error('hCaptcha secret key not configured');

            return false;
        }

        try {
            $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $remoteIp ?? request()->ip(),
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success'])) {
                return $result['success'] === true;
            }

            if (config('logging.dev_log')) {
                Log::warning('hCaptcha verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'response' => $result,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('hCaptcha verification exception', [
                    'message' => $e->getMessage(),
                    'token' => substr($token, 0, 10).'...',
                ]);
            }

            return false;
        }
    }

    /**
     * Verify any supported captcha type
     */
    public function verify(string $type, string $token, ?string $remoteIp = null): bool
    {
        return match ($type) {
            'turnstile' => $this->verifyTurnstile($token, $remoteIp),
            'hcaptcha' => $this->verifyHCaptcha($token, $remoteIp),
            default => false,
        };
    }
}
