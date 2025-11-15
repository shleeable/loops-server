<?php

namespace App\Services;

use App\Models\Hashtag;
use Illuminate\Support\Facades\Cache;

class ExploreService
{
    const TRENDING_TAGS_KEY = 'explore:trending_tags';

    public function getTrendingTags($refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::TRENDING_TAGS_KEY);
        }

        return Cache::remember(self::TRENDING_TAGS_KEY, now()->addWeek(), function () {
            return Hashtag::select(['id', 'name', 'count'])
                ->where('count', '>', 9)
                ->where('can_trend', true)
                ->orderByDesc('count')
                ->limit(12)
                ->get()
                ->toArray();
        });
    }
}
