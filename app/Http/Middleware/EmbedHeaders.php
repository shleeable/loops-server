<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmbedHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', $this->buildCsp());
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy',
            'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'
        );

        if (app()->environment('production')) {
            $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=3600');
        } else {
            $response->headers->set('Cache-Control', 'no-store');
        }

        return $response;
    }

    protected function buildCsp(): string
    {
        $s3 = config('filesystems.disks.s3.url');

        $scriptSrc = ["'self'"];
        $styleSrc = ["'self'", "'unsafe-inline'"];
        $connectSrc = ["'self'"];
        $fontSrc = ["'self'", 'data:'];
        $imgSrc = ["'self'", $s3, 'data:'];
        $mediaSrc = ["'self'", $s3, 'blob:'];
        $workerSrc = ["'self'"];

        if (! app()->environment('production')) {
            $viteHost = config('app.vite_dev_host', 'https://loops-server.test:5173');
            $viteWs = preg_replace('#^https?://#', 'wss://', $viteHost);
            $viteWsAlt = preg_replace('#^https?://#', 'ws://', $viteHost);

            $scriptSrc[] = $viteHost;
            $styleSrc[] = $viteHost;
            $connectSrc[] = $viteHost;
            $connectSrc[] = $viteWs;
            $connectSrc[] = $viteWsAlt;
            $fontSrc[] = $viteHost;
            $imgSrc[] = $viteHost;
            $workerSrc[] = 'blob:';
        }

        $directives = [
            'frame-ancestors *',
            "default-src 'self'",
            'media-src '.implode(' ', $mediaSrc),
            'img-src '.implode(' ', $imgSrc),
            'script-src '.implode(' ', $scriptSrc),
            'style-src '.implode(' ', $styleSrc),
            'connect-src '.implode(' ', $connectSrc),
            'font-src '.implode(' ', $fontSrc),
            'worker-src '.implode(' ', $workerSrc),
        ];

        return implode('; ', $directives).';';
    }
}
