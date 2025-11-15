<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NodeinfoCrawlerService
{
    /**
     * Return ['name' => string, 'version' => string] or null if not found.
     */
    public function getSoftware(string $domain): ?array
    {
        $scheme = 'https';
        $base = $this->buildBaseUrl($domain, $scheme);

        try {
            $wellKnownUrl = rtrim($base, '/').'/.well-known/nodeinfo';

            $wellKnown = $this->getJson($wellKnownUrl);
            if (! $wellKnown || ! isset($wellKnown['links']) || ! is_array($wellKnown['links'])) {
                return null;
            }

            $nodeInfoUrl = $this->pickBestNodeinfoUrl($wellKnown['links']);
            if (! $nodeInfoUrl) {
                return null;
            }

            $nodeInfo = $this->getJson($nodeInfoUrl);
            if (! $nodeInfo) {
                return null;
            }

            $name = Arr::get($nodeInfo, 'software.name');
            $version = Arr::get($nodeInfo, 'software.version');

            if (is_string($name) && is_string($version) && $name !== '' && $version !== '') {
                return ['name' => $name, 'version' => $version];
            }

            if (is_string($name) && $name !== '') {
                return ['name' => $name, 'version' => 'unknown'];
            }
        } catch (\Throwable $e) {
            throw new \Exception('Unable to find valid nodeinfo for domain: '.$domain);
        }

        return null;
    }

    /**
     * Build a clean base URL from a domain or URL-ish input.
     */
    protected function buildBaseUrl(string $input, string $scheme = 'https'): string
    {
        $trimmed = trim($input);

        if (Str::startsWith($trimmed, ['http://', 'https://'])) {
            $parts = parse_url($trimmed);
            $host = $parts['host'] ?? $trimmed;

            $port = isset($parts['port']) ? ':'.$parts['port'] : '';

            return "{$parts['scheme']}://{$host}{$port}";
        }

        // Otherwise assume it's a bare domain like "pixelfed.social"
        return $scheme.'://'.$trimmed;
    }

    /**
     * Pick the best NodeInfo URL, preferring 2.1 over 2.0 (aka "v2").
     *
     * Spec rels typically look like:
     *   http://nodeinfo.diaspora.software/ns/schema/2.1
     *   http://nodeinfo.diaspora.software/ns/schema/2.0
     */
    protected function pickBestNodeinfoUrl(array $links): ?string
    {
        $byRel = [];
        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }
            $rel = $link['rel'] ?? null;
            $href = $link['href'] ?? null;
            if (is_string($rel) && is_string($href)) {
                $byRel[$rel] = $href;
            }
        }

        $preferredRels = [
            'http://nodeinfo.diaspora.software/ns/schema/2.1',
            'https://nodeinfo.diaspora.software/ns/schema/2.1',
            'http://nodeinfo.diaspora.software/ns/schema/2.0',
            'https://nodeinfo.diaspora.software/ns/schema/2.0',
        ];

        foreach ($preferredRels as $rel) {
            if (isset($byRel[$rel])) {
                return $byRel[$rel];
            }
        }

        $candidates = array_values(array_filter($byRel, function ($href) {
            return preg_match('~/nodeinfo/(2\.[01])~', $href);
        }));

        usort($candidates, function ($a, $b) {
            $va = preg_match('~/nodeinfo/(2\.[01])~', $a, $ma) ? $ma[1] : '0.0';
            $vb = preg_match('~/nodeinfo/(2\.[01])~', $b, $mb) ? $mb[1] : '0.0';

            return version_compare($vb, $va);
        });

        return $candidates[0] ?? null;
    }

    /**
     * GET JSON with sane defaults: retries, timeout, redirects, JSON accept header.
     */
    protected function getJson(string $url): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => app('user_agent'),
            'Accept' => 'application/json',
        ])
            ->retry(2, 200)
            ->timeout(8)
            ->withOptions([
                'allow_redirects' => true,
            ])
            ->get($url);

        if (! $response->ok()) {
            return null;
        }

        try {
            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
