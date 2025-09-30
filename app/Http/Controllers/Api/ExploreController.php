<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Models\Hashtag;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getTrendingTags(Request $request)
    {
        return Cache::remember('trending_tags', 3600, function () {
            return Hashtag::select(['id', 'name', 'count'])
                ->where('count', '>', 10)
                ->where('can_trend', true)
                ->orderByDesc('count')
                ->limit(12)
                ->get()
                ->toArray();
        });
    }

    public function getTagFeed(Request $request, $id)
    {
        $cacheKey = "tag_feed_{$id}";

        $res = Cache::remember($cacheKey, 21600, function () use ($id) {
            return $this->fetchTagFeedData($id);
        });

        return $this->data($res);
    }

    private function fetchTagFeedData($tagName)
    {
        $hashtag = Hashtag::select(['id', 'name', 'count'])
            ->where('name_normalized', $tagName)
            ->where('count', '>', 10)
            ->first();

        if (! $hashtag) {
            return collect();
        }

        $videos = $this->getOptimizedTagVideos($hashtag->id);

        return $this->batchProcessVideoData($videos);
    }

    private function getOptimizedTagVideos($hashtagId)
    {
        return Video::select([
            'videos.id',
            'videos.created_at',
            'videos.comments',
            'videos.likes',
            'videos.status',
            DB::raw('((videos.likes + (videos.comments * 2)) / POW(TIMESTAMPDIFF(MINUTE, videos.created_at, NOW()) / 60.0 + 2, 1.2)) as trending_score'),
        ])
            ->join('video_hashtags', 'videos.id', '=', 'video_hashtags.video_id')
            ->where('video_hashtags.hashtag_id', $hashtagId)
            ->where('videos.status', 2)
            ->where('videos.likes', '>', 10)
            ->orderByDesc('trending_score')
            ->limit(30)
            ->get();
    }

    private function batchProcessVideoData($videos)
    {
        $videoIds = $videos->pluck('id')->toArray();

        $processedVideos = collect();

        foreach ($videos as $video) {
            try {
                $mediaData = VideoService::getMediaData($video->id);
                if ($mediaData) {
                    $processedVideos->push($mediaData);
                }

                if ($processedVideos->count() >= 15) {
                    break;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to process tag video {$video->id}: ".$e->getMessage());

                continue;
            }
        }

        return $processedVideos;
    }

    public function clearTagCache($tagName)
    {
        $cacheKey = "tag_feed_{$tagName}";
        Cache::forget($cacheKey);
    }

    public function warmTagCaches()
    {
        $popularTags = Hashtag::select('name_normalized')
            ->where('count', '>', 100)
            ->where('can_trend', true)
            ->orderByDesc('count')
            ->limit(20)
            ->pluck('name_normalized');

        foreach ($popularTags as $tagName) {
            $this->fetchTagFeedData($tagName);
        }
    }
}
