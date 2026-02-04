<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasHttpSignature
{
    /**
     * Quick check if request has a signature header
     */
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

        return $next($request);
    }
}
