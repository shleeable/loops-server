<?php

namespace App\Support;

class ViteAssets
{
    protected static ?array $manifest = null;

    protected static function manifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        $path = public_path('build/manifest.json');

        if (! file_exists($path)) {
            return self::$manifest = [];
        }

        return self::$manifest = json_decode(file_get_contents($path), true) ?? [];
    }

    public static function preloadFont(string $pattern, string $type = 'font/woff2'): string
    {
        $manifest = self::manifest();

        foreach ($manifest as $entry) {
            foreach ($entry['assets'] ?? [] as $asset) {
                if (str_contains($asset, $pattern)) {
                    $url = asset('build/'.$asset);

                    return sprintf(
                        '<link rel="preload" href="%s" as="font" type="%s" crossorigin>',
                        e($url),
                        e($type)
                    );
                }
            }
        }

        return '';
    }
}
