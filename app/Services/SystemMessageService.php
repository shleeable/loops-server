<?php

namespace App\Services;

use App\Models\SystemMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SystemMessageService
{
    const CACHE_KEY = 'loops:services:sysmsg:';

    public function get($id)
    {
        $key = self::CACHE_KEY.'get:'.$id;

        return Cache::remember($key, now()->addHours(72), function () use ($id) {
            $sm = SystemMessage::active()->find($id);

            if (! $sm) {
                return [];
            }

            return [
                'id' => $sm->key_id,
                'title' => $sm->title,
                'summary' => Str::limit(app(SanitizeService::class)->cleanHtmlWithSpacing($sm->body), 80),
                'link' => $sm->link,
                'published_at' => $sm->published_at->format('c'),
            ];
        });
    }

    public function getFull($id)
    {
        $key = self::CACHE_KEY.'getFull:'.$id;

        return Cache::remember($key, now()->addHours(72), function () use ($id) {
            $sm = SystemMessage::active()->find($id);

            if (! $sm) {
                return [];
            }

            $type = match ($sm->type) {
                8 => 'info',
                9 => 'feature',
                10 => 'update',
                default => 'info'
            };

            return [
                'id' => $sm->key_id,
                'type' => $type,
                'title' => $sm->title,
                'body' => $sm->body,
                'link' => $sm->link,
                'published_at' => $sm->published_at->format('c'),
            ];
        });
    }
}
