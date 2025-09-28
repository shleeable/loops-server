<?php

namespace App\Services;

use App\Models\CommentLike;
use App\Models\CommentReplyLike;
use App\Models\VideoLike;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class LikeService
{
    private const VIDEO_LIKES_KEY = 'api:s:like:video:';

    private const COMMENT_LIKES_KEY = 'api:s:like:comment:';

    private const COMMENT_REPLY_LIKES_KEY = 'api:s:like:reply:';

    private const VIDEO_CACHE_LOADED_KEY = 'api:s:like:loaded:video:';

    private const COMMENT_CACHE_LOADED_KEY = 'api:s:like:loaded:comment:';

    private const REPLY_CACHE_LOADED_KEY = 'api:s:like:loaded:reply:';

    private const CACHE_TTL = 3600;

    private const MAX_CACHED_LIKES = 5000;

    private const CACHE_WARMING_BATCH_SIZE = 100;

    /**
     * Check if a profile has liked a video
     */
    public function hasLikedVideo(string $videoId, string $profileId): bool
    {
        return $this->hasLiked(
            self::VIDEO_LIKES_KEY.$videoId,
            self::VIDEO_CACHE_LOADED_KEY.$videoId,
            $profileId,
            fn () => VideoLike::where('video_id', $videoId)
                ->where('profile_id', $profileId)
                ->exists()
        );
    }

    /**
     * Check if a profile has liked a comment
     */
    public function hasLikedComment(string $commentId, string $profileId): bool
    {
        return $this->hasLiked(
            self::COMMENT_LIKES_KEY.$commentId,
            self::COMMENT_CACHE_LOADED_KEY.$commentId,
            $profileId,
            fn () => CommentLike::where('comment_id', $commentId)
                ->where('profile_id', $profileId)
                ->exists()
        );
    }

    /**
     * Check if a profile has liked a comment reply
     */
    public function hasLikedReply(string $replyId, string $profileId): bool
    {
        return $this->hasLiked(
            self::COMMENT_REPLY_LIKES_KEY.$replyId,
            self::REPLY_CACHE_LOADED_KEY.$replyId,
            $profileId,
            fn () => CommentReplyLike::where('comment_id', $replyId)
                ->where('profile_id', $profileId)
                ->exists()
        );
    }

    /**
     * Add a video like
     */
    public function addVideoLike(string $videoId, string $profileId): bool
    {
        return $this->addLike(
            self::VIDEO_LIKES_KEY.$videoId,
            self::VIDEO_CACHE_LOADED_KEY.$videoId,
            $profileId
        );
    }

    /**
     * Add a comment like
     */
    public function addCommentLike(string $commentId, string $profileId): bool
    {
        return $this->addLike(
            self::COMMENT_LIKES_KEY.$commentId,
            self::COMMENT_CACHE_LOADED_KEY.$commentId,
            $profileId
        );
    }

    /**
     * Add a comment reply like
     */
    public function addReplyLike(string $replyId, string $profileId): bool
    {
        return $this->addLike(
            self::COMMENT_REPLY_LIKES_KEY.$replyId,
            self::REPLY_CACHE_LOADED_KEY.$replyId,
            $profileId
        );
    }

    /**
     * Remove a video like
     */
    public function removeVideoLike(string $videoId, string $profileId): bool
    {
        return $this->removeLike(
            self::VIDEO_LIKES_KEY.$videoId,
            $profileId
        );
    }

    /**
     * Remove a comment like
     */
    public function removeCommentLike(string $commentId, string $profileId): bool
    {
        return $this->removeLike(
            self::COMMENT_LIKES_KEY.$commentId,
            $profileId
        );
    }

    /**
     * Remove a comment reply like
     */
    public function removeReplyLike(string $replyId, string $profileId): bool
    {
        return $this->removeLike(
            self::COMMENT_REPLY_LIKES_KEY.$replyId,
            $profileId
        );
    }

    /**
     * Get all profiles that liked a video
     */
    public function getVideoLikers(string $videoId): array
    {
        $cacheKey = self::VIDEO_LIKES_KEY.$videoId;
        $loadedKey = self::VIDEO_CACHE_LOADED_KEY.$videoId;

        $this->warmCacheIfNeeded(
            $cacheKey,
            $loadedKey,
            fn () => VideoLike::where('video_id', $videoId)
                ->orderBy('created_at', 'desc')
                ->limit(self::MAX_CACHED_LIKES)
                ->pluck('profile_id')
        );

        return Redis::smembers($cacheKey);
    }

    /**
     * Get like count for a video
     */
    public function getVideoLikeCount(string $videoId): int
    {
        $cacheKey = self::VIDEO_LIKES_KEY.$videoId;
        $loadedKey = self::VIDEO_CACHE_LOADED_KEY.$videoId;

        if (Redis::exists($loadedKey)) {
            return Redis::scard($cacheKey);
        }

        return VideoLike::where('video_id', $videoId)->count();
    }

    /**
     * Bulk check if profiles have liked videos
     */
    public function bulkHasLikedVideos(array $videoIds, string $profileId): array
    {
        $result = [];

        $pipeline = Redis::pipeline();
        foreach ($videoIds as $videoId) {
            $pipeline->sismember(self::VIDEO_LIKES_KEY.$videoId, $profileId);
        }
        $cached = $pipeline->exec();

        foreach ($videoIds as $index => $videoId) {
            $cacheKey = self::VIDEO_LIKES_KEY.$videoId;
            $loadedKey = self::VIDEO_CACHE_LOADED_KEY.$videoId;

            if (Redis::exists($loadedKey)) {
                $result[$videoId] = (bool) $cached[$index];
            } else {
                $result[$videoId] = VideoLike::where('video_id', $videoId)
                    ->where('profile_id', $profileId)
                    ->exists();
            }
        }

        return $result;
    }

    /**
     * Clear cache for a specific video
     */
    public function clearVideoCache(string $videoId): void
    {
        Redis::del([
            self::VIDEO_LIKES_KEY.$videoId,
            self::VIDEO_CACHE_LOADED_KEY.$videoId,
        ]);
    }

    /**
     * Clear cache for a specific comment
     */
    public function clearCommentCache(string $commentId): void
    {
        Redis::del([
            self::COMMENT_LIKES_KEY.$commentId,
            self::COMMENT_CACHE_LOADED_KEY.$commentId,
        ]);
    }

    /**
     * Warm cache for a video
     */
    public function warmVideoCache(string $videoId): void
    {
        $cacheKey = self::VIDEO_LIKES_KEY.$videoId;
        $loadedKey = self::VIDEO_CACHE_LOADED_KEY.$videoId;

        $likes = VideoLike::where('video_id', $videoId)
            ->orderBy('created_at', 'desc')
            ->limit(self::MAX_CACHED_LIKES)
            ->pluck('profile_id');

        if ($likes->isNotEmpty()) {
            Redis::sadd($cacheKey, ...$likes->toArray());
            Redis::expire($cacheKey, self::CACHE_TTL);
            Redis::setex($loadedKey, self::CACHE_TTL, 1);
        }
    }

    /**
     * Generic method to check if item is liked
     */
    private function hasLiked(string $cacheKey, string $loadedKey, string $profileId, callable $dbCheck): bool
    {
        // If cache is loaded, use it
        if (Redis::exists($loadedKey)) {
            return Redis::sismember($cacheKey, $profileId) === 1;
        }

        // Otherwise check database
        return $dbCheck();
    }

    /**
     * Generic method to add a like to cache
     */
    private function addLike(string $cacheKey, string $loadedKey, string $profileId): bool
    {
        // Only add to cache if cache is already loaded
        if (Redis::exists($loadedKey)) {
            if (Redis::sismember($cacheKey, $profileId) === 1) {
                return false;
            }

            Redis::sadd($cacheKey, $profileId);

            Redis::expire($cacheKey, self::CACHE_TTL);
            Redis::expire($loadedKey, self::CACHE_TTL);

            return true;
        }

        return true;
    }

    /**
     * Generic method to remove a like from cache
     */
    private function removeLike(string $cacheKey, string $profileId): bool
    {
        return Redis::srem($cacheKey, $profileId) > 0;
    }

    /**
     * Warm cache if needed
     */
    private function warmCacheIfNeeded(string $cacheKey, string $loadedKey, callable $dataFetcher): void
    {
        if (! Redis::exists($loadedKey)) {
            $data = $dataFetcher();

            if ($data instanceof Collection && $data->isNotEmpty()) {
                Redis::sadd($cacheKey, ...$data->toArray());
                Redis::expire($cacheKey, self::CACHE_TTL);
                Redis::setex($loadedKey, self::CACHE_TTL, 1);
            }
        }
    }
}
