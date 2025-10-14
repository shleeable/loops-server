<?php

namespace App\Http\Middleware;

use App\Services\ConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FederationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = app(ConfigService::class);

        if (! $config->federation()) {
            return abort(404, 'Not found');
        }

        return $next($request);
    }
}
