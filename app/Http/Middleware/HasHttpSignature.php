<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasHttpSignature
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('Signature');

        if (! $signature) {
            return response()->json(['error' => 'Missing signature header'], 401);
        }

        if (! str_contains($signature, 'keyId=') || ! str_contains($signature, 'signature=')) {
            return response()->json(['error' => 'Malformed signature header'], 401);
        }

        $request->attributes->set('raw_signature', $signature);
        $request->attributes->set('raw_headers', $request->headers->all());

        $keyId = $this->extractKeyId($signature);
        if ($keyId) {
            $actorUrl = preg_replace('/#.*$/', '', $keyId);
            $originDomain = parse_url($actorUrl, PHP_URL_HOST);
            if ($originDomain) {
                $request->attributes->set('activitypub_origin_domain', strtolower($originDomain));
            }
        }

        return $next($request);
    }

    protected function extractKeyId(string $signature): ?string
    {
        if (preg_match('/keyId="([^"]+)"/', $signature, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
