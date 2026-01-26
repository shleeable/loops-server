<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushRelayClientService
{
    protected $domain;

    protected $secret;

    public function __construct()
    {
        $this->domain = parse_url(config('app.url'), PHP_URL_HOST);
    }

    public function ensureAttested(): bool
    {
        if ($this->secret) {
            return true;
        }

        $this->secret = Cache::get('push_relay_secret');

        if ($this->secret) {
            return true;
        }

        return $this->attest();
    }

    protected function attest(): bool
    {
        $url = app('push_relay').'/api/v1/attest';
        $path = '/api/v1/attest';
        $body = json_encode([]);

        try {
            $privateKey = app(SigningService::class)->getPrivateKey();
            $publicKeyId = app(InstanceActorService::class)->permalink('#main-key');

            $date = now()->format('D, d M Y H:i:s \G\M\T');
            $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));

            $headersToSign = [
                '(request-target)' => 'post '.$path,
                'host' => parse_url(app('push_relay'), PHP_URL_HOST),
                'date' => $date,
                'digest' => $digest,
            ];

            $signingString = $this->buildSigningString($headersToSign);

            openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $signatureB64 = base64_encode($signature);

            $signatureHeader = sprintf(
                'keyId="%s",headers="(request-target) host date digest",signature="%s"',
                $publicKeyId,
                $signatureB64
            );

            $response = Http::timeout(10)->withHeaders([
                'Date' => $date,
                'Digest' => $digest,
                'Signature' => $signatureHeader,
                'Content-Type' => 'application/json',
            ])->post($url, []);

            if ($response->successful()) {
                $data = $response->json();
                $this->secret = $data['secret'];

                Cache::put('push_relay_secret', $this->secret, 60 * 60 * 24 * 365);

                if (config('logging.dev_log')) {
                    Log::info('Successfully attested with push relay', [
                        'domain' => $this->domain,
                        'relay' => app('push_relay'),
                    ]);
                }

                return true;
            }

            Log::error('Failed to attest with push relay', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception during attestation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function notify(string $expoPushToken, string $message, array $data = []): bool
    {
        if (! $this->ensureAttested()) {
            Log::warning('Cannot send notification: attestation failed');

            return false;
        }

        $url = app('push_relay').'/api/v1/notify';

        try {
            $response = Http::timeout(10)->withHeaders([
                'X-Loops-Domain' => $this->domain,
                'X-Loops-Secret' => $this->secret,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'token' => $expoPushToken,
                'message' => $message,
                'data' => $data,
            ]);

            if ($response->successful()) {
                return true;
            }

            if ($response->status() === 401) {
                Log::warning('Push relay returned 401, invalidating cached secret and re-attesting');
                Cache::forget('push_relay_secret');
                $this->secret = null;

                if ($this->ensureAttested()) {
                    return $this->notify($expoPushToken, $message, $data);
                }
            }

            if (config('logging.dev_log')) {
                Log::error('Failed to send notification', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Exception sending notification', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function notifyBatch(array $notifications): array
    {
        if (! $this->ensureAttested()) {
            Log::warning('Cannot send batch notifications: attestation failed');

            return ['success' => false, 'error' => 'Attestation failed'];
        }

        $url = app('push_relay').'/api/v1/notify/batch';

        try {
            $response = Http::timeout(30)->withHeaders([
                'X-Loops-Domain' => $this->domain,
                'X-Loops-Secret' => $this->secret,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'notifications' => $notifications,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 401) {
                Log::warning('Push relay returned 401 for batch, re-attesting');
                Cache::forget('push_relay_secret');
                $this->secret = null;

                if ($this->ensureAttested()) {
                    return $this->notifyBatch($notifications);
                }
            }

            Log::error('Failed to send batch notifications', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->body()];

        } catch (\Exception $e) {
            Log::error('Exception sending batch notifications', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function health(): bool
    {
        try {
            $response = Http::timeout(5)->get(app('push_relay').'/api/v1/health');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function forceAttest(): bool
    {
        Cache::forget('push_relay_secret');
        $this->secret = null;

        return $this->attest();
    }

    public function isAttested(): bool
    {
        return $this->ensureAttested();
    }

    protected function buildSigningString(array $headers): string
    {
        return implode("\n", array_map(function ($key, $value) {
            return strtolower($key).': '.$value;
        }, array_keys($headers), $headers));
    }
}
