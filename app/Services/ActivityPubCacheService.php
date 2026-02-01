<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ActivityPubCacheService
{
    protected $ttl = 3600;

    protected $enabled = true;

    public function __construct()
    {
        $this->enabled = true;
        $this->ttl = config('loops.federation.cache_ttl', 3600);
    }

    /**
     * Get or cache an ActivityPub object
     */
    public function getOrCache(string $key, callable $callback, ?int $ttl = null)
    {
        if (! $this->enabled) {
            return $callback();
        }

        $cacheKey = "ap:object:{$key}";
        $ttl = $ttl ?? $this->ttl;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Invalidate cache for a specific object
     */
    public function forget(string $key)
    {
        $cacheKey = "ap:object:{$key}";
        Cache::forget($cacheKey);
    }

    /**
     * Invalidate multiple cache keys at once
     */
    public function forgetMultiple(array $keys)
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Generate cache key for profile
     */
    public function profileKey(int $profileId): string
    {
        return "profile:{$profileId}";
    }

    /**
     * Generate cache key for video
     */
    public function videoKey(int $videoId): string
    {
        return "video:{$videoId}";
    }

    /**
     * Generate cache key for comment
     */
    public function commentKey(int $commentId): string
    {
        return "comment:{$commentId}";
    }

    /**
     * Generate cache key for comment reply
     */
    public function replyKey(int $replyId): string
    {
        return "reply:{$replyId}";
    }

    /**
     * Get ETag for response
     */
    public function getETag(string $content): string
    {
        return md5($content);
    }
}
