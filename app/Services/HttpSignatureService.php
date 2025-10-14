<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HttpSignatureService
{
    /**
     * Sign an HTTP request
     *
     * @param  string  $keyId  The key identifier (actor URL + #main-key)
     * @param  string  $privateKey  The private key PEM
     * @param  array  $headers  The headers to include in signature
     * @param  string  $method  The HTTP method
     * @param  string  $path  The request path
     * @param  string|null  $body  The request body for digest
     * @return string The Signature header value
     */
    public function sign(
        string $keyId,
        string $privateKey,
        array $headers,
        string $method,
        string $path,
        ?string $body = null
    ): string {
        $signatureHeaders = ['(request-target)', 'host', 'date'];

        if ($body !== null) {
            $digest = $this->calculateDigest($body);
            $headers['Digest'] = $digest;
            $signatureHeaders[] = 'digest';
        }

        $signatureString = $this->buildSignatureString($signatureHeaders, $headers, $method, $path);

        $signature = '';
        $key = openssl_pkey_get_private($privateKey);

        if (! $key) {
            throw new \RuntimeException('Invalid private key');
        }

        if (! openssl_sign($signatureString, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Failed to sign data');
        }

        return sprintf(
            'keyId="%s",algorithm="rsa-sha256",headers="%s",signature="%s"',
            $keyId,
            implode(' ', $signatureHeaders),
            base64_encode($signature)
        );
    }

    public function instanceSign(
        array $headers,
        string $method,
        string $path,
        ?string $body = null
    ): string {
        $signatureHeaders = ['(request-target)', 'host', 'date'];

        if ($body !== null) {
            $digest = $this->calculateDigest($body);
            $headers['Digest'] = $digest;
            $signatureHeaders[] = 'digest';
        }

        $signatureString = $this->buildSignatureString($signatureHeaders, $headers, $method, $path);

        $signature = '';
        $privateKey = app(SigningService::class)->getPrivateKey();
        $keyId = url('/ap/actor#main-key');
        $key = openssl_pkey_get_private($privateKey);

        if (! $key) {
            throw new \RuntimeException('Invalid private key');
        }

        if (! openssl_sign($signatureString, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Failed to sign data');
        }

        return sprintf(
            'keyId="%s",algorithm="rsa-sha256",headers="%s",signature="%s"',
            $keyId,
            implode(' ', $signatureHeaders),
            base64_encode($signature)
        );
    }

    /**
     * Verify an HTTP signature
     *
     * @param  string  $signatureHeader  The Signature header value
     * @param  string  $publicKey  The public key PEM
     * @param  array  $headers  The request headers
     * @param  string  $method  The HTTP method
     * @param  string  $path  The request path
     */
    public function verify(
        string $signatureHeader,
        string $publicKey,
        array $headers,
        string $method,
        string $path
    ): bool {
        try {
            $parsed = $this->parseSignatureHeader($signatureHeader);

            if (! isset($parsed['signature']) || ! isset($parsed['headers'])) {
                Log::debug('Invalid signature header format', ['parsed' => $parsed]);

                return false;
            }

            $signatureHeaders = explode(' ', $parsed['headers']);

            $signatureString = $this->buildSignatureString($signatureHeaders, $headers, $method, $path);

            $signature = base64_decode($parsed['signature']);

            if ($signature == false) {
                Log::debug('Failed to decode signature');

                return false;
            }

            $key = openssl_pkey_get_public($publicKey);

            if (! $key) {
                Log::debug('Invalid public key');

                return false;
            }

            $result = openssl_verify($signatureString, $signature, $key, OPENSSL_ALGO_SHA256);

            return $result === 1;
        } catch (\Exception $e) {
            Log::error('Exception during signature verification', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build the signature string
     */
    protected function buildSignatureString(
        array $signatureHeaders,
        array $headers,
        string $method,
        string $path
    ): string {
        $parts = [];

        foreach ($signatureHeaders as $header) {
            $header = strtolower($header);

            if ($header === '(request-target)') {
                $parts[] = '(request-target): '.strtolower($method).' '.$path;
            } else {
                $value = $this->findHeader($headers, $header);

                if ($value === null) {
                    throw new \RuntimeException("Missing required header: {$header}");
                }

                $parts[] = $header.': '.$value;
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Find a header case-insensitively
     */
    protected function findHeader(array $headers, string $name): ?string
    {
        $name = strtolower($name);

        foreach ($headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Parse the Signature header
     */
    public function parseSignatureHeader(string $signature): array
    {
        $parts = [];

        preg_match_all('/(\w+)="([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', $signature, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $parts[$match[1]] = str_replace('\\"', '"', $match[2]);
        }

        return $parts;
    }

    /**
     * Calculate digest for request body
     */
    protected function calculateDigest(string $body): string
    {
        return 'SHA-256='.base64_encode(hash('sha256', $body, true));
    }

    /**
     * Verify digest header
     */
    public function verifyDigest(string $digestHeader, string $body): bool
    {
        $parts = explode('=', $digestHeader, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$algorithm, $expectedDigest] = $parts;

        switch (strtoupper($algorithm)) {
            case 'SHA-256':
                $actualDigest = base64_encode(hash('sha256', $body, true));
                break;
            case 'SHA-512':
                $actualDigest = base64_encode(hash('sha512', $body, true));
                break;
            default:
                return false;
        }

        return hash_equals($expectedDigest, $actualDigest);
    }
}
