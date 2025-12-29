<?php

namespace App\Services;

use App\Models\BlockedDomain;
use Illuminate\Support\Facades\Cache;

class BlockedDomainService
{
    private const CACHE_KEY = 'loops:api:s:blocked_domains';

    private const CACHE_TTL = 3600;

    public static function isBlocked(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        $host = strtolower($host);
        $blockedDomains = self::getBlockedDomains();

        if (in_array($host, $blockedDomains['exact'])) {
            return true;
        }

        foreach ($blockedDomains['patterns'] as $pattern) {
            if (self::matchesDomain($host, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function getBlockedDomains(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $blocked = BlockedDomain::all();

            return [
                'exact' => $blocked->where('is_subdomain', false)->pluck('domain')->toArray(),
                'patterns' => $blocked->where('is_subdomain', true)->pluck('domain')->toArray(),
            ];
        });
    }

    private static function matchesDomain(string $host, string $pattern): bool
    {
        if ($host === $pattern) {
            return true;
        }

        return str_ends_with($host, '.'.$pattern);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function validateUrl(string $url): array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'error' => 'Invalid URL format',
            ];
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'])) {
            return [
                'valid' => false,
                'error' => 'Only HTTP and HTTPS URLs are allowed',
            ];
        }

        if (self::isBlocked($url)) {
            return [
                'valid' => false,
                'error' => 'This domain is not allowed',
            ];
        }

        return [
            'valid' => true,
        ];
    }
}
