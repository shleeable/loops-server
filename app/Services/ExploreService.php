<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\VideoHashtag;
use Illuminate\Support\Facades\Cache;

class ExploreService
{
    const TRENDING_TAGS_KEY = 'explore:trending_tags';

    const GUEST_TAG_FEED_KEY = 'explore:getTagFeed_v1:';

    public function getTrendingTags($refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::TRENDING_TAGS_KEY);
        }

        return Cache::remember(self::TRENDING_TAGS_KEY, now()->addWeek(), function () {
            return Hashtag::select(['id', 'name', 'count'])
                ->where('can_trend', true)
                ->orderByDesc('count')
                ->limit(20)
                ->get()
                ->toArray();
        });
    }

    public function getGuestTagFeed($hashtagId, $refresh = false)
    {
        $key = self::GUEST_TAG_FEED_KEY.$hashtagId.':v2';
        $minLikes = (int) config('loops.explore.tags.min_likes.guest', 10);
        $limit = 22;

        if ($refresh) {
            Cache::forget($key);
        }

        $candidateHashtags = Cache::remember($key, now()->addHours(48), function () use ($hashtagId, $minLikes) {
            $base = fn () => VideoHashtag::where('video_hashtags.hashtag_id', $hashtagId)
                ->where('video_hashtags.visibility', 1)
                ->join('videos', 'videos.id', '=', 'video_hashtags.video_id')
                ->join('profiles', 'profiles.id', '=', 'videos.profile_id')
                ->where('videos.status', 2)
                ->where('videos.likes', '>', $minLikes)
                ->where('profiles.status', 1)
                ->select(
                    'video_hashtags.*',
                    'videos.profile_id as video_profile_id',
                    'videos.likes as video_likes',
                    'videos.created_at as video_created_at'
                );

            $recent = $base()->orderByDesc('video_hashtags.id')->limit(200)->get();
            $top = $base()->orderByDesc('videos.likes')->limit(100)->get();

            return $recent->concat($top)->unique('video_id')->values();
        });

        $gravity = 1.8;

        $ranked = $candidateHashtags
            // @phpstan-ignore-next-line
            ->sortByDesc(fn ($vh) => $this->decayedScore($vh->video_likes, $vh->video_created_at, $gravity))
            ->values();

        $seenProfiles = [];
        $results = collect();

        foreach ($ranked as $videoHashtag) {
            // @phpstan-ignore-next-line
            $profileId = $videoHashtag->video_profile_id;

            if ($profileId && isset($seenProfiles[$profileId])) {
                continue;
            }

            $videoData = app(VideoService::class)->getMediaData($videoHashtag->video_id);
            if (empty($videoData)) {
                continue;
            }

            if (data_get($videoData, 'likes', 0) < $minLikes) {
                continue;
            }

            $profileId = $profileId ?: data_get($videoData, 'account.id');
            if (! $profileId || isset($seenProfiles[$profileId])) {
                continue;
            }

            if (! app(AccountService::class)->get($profileId)) {
                continue;
            }

            $seenProfiles[$profileId] = true;
            $results->push($videoHashtag);

            if ($results->count() >= $limit) {
                break;
            }
        }

        return $results->values();
    }

    protected function decayedScore($likes, $createdAt, float $gravity = 1.5): float
    {
        $likes = max(0, (int) $likes);

        if (! $createdAt) {
            return 0.0;
        }

        $hours = max(0, now()->diffInMinutes(now()->parse($createdAt)) / 60);

        return $likes / pow($hours + 2, $gravity);
    }
}
