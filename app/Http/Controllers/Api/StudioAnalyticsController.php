<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StudioAnalyticsController extends Controller
{
    public function videoViews(Request $request)
    {
        $profileId = $request->user()->profile_id;
        $since = Carbon::now()->subDays(30)->startOfDay();

        $views = Cache::remember('studio:anal:views:pid:'.$profileId, now()->addHours(6), function () use ($profileId, $since) {
            return DB::table('feed_impressions')
                ->join('videos', 'videos.id', '=', 'feed_impressions.video_id')
                ->where('videos.profile_id', $profileId)
                ->where('feed_impressions.viewed_at', '>=', $since)
                ->select(
                    DB::raw('DATE(feed_impressions.viewed_at) as date'),
                    DB::raw('COUNT(feed_impressions.profile_id) as views')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');
        });

        $series = collect(range(0, 29))->map(function ($i) use ($since, $views) {
            $date = $since->copy()->addDays($i)->toDateString();

            return [
                'date' => $date,
                'views' => (int) ($views[$date]->views ?? 0),
            ];
        });

        return response()->json([
            'data' => $series->values(),
            'total' => $series->sum('views'),
        ]);
    }

    public function newFollowers(Request $request)
    {
        $profileId = $request->user()->profile_id;
        $since = Carbon::now()->subDays(30)->startOfDay();

        $followers = Cache::remember('studio:anal:followers:pid:'.$profileId, now()->addHours(6), function () use ($profileId, $since) {
            return DB::table('followers')
                ->where('following_id', $profileId)
                ->where('created_at', '>=', $since)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(profile_id) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');
        });

        $series = collect(range(0, 29))->map(function ($i) use ($since, $followers) {
            $date = $since->copy()->addDays($i)->toDateString();

            return [
                'date' => $date,
                'count' => (int) ($followers[$date]->count ?? 0),
            ];
        });

        return response()->json([
            'data' => $series->values(),
            'total' => $series->sum('count'),
        ]);
    }

    public function profileLinks(Request $request)
    {
        $profileId = $request->user()->profile_id;

        $res = ProfileLink::whereProfileId($profileId)
            ->get()
            ->map(fn ($link) => ['url' => $link->url, 'clicks' => $link->click_count]);

        return response()->json([
            'data' => $res,
        ]);
    }
}
