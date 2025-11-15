<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoHashtagResource;
use App\Models\VideoHashtag;
use App\Services\ExploreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExploreController extends Controller
{
    use ApiHelpers;

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function getTrendingTags(Request $request)
    {
        return $this->data(app(ExploreService::class)->getTrendingTags());
    }

    public function getTagFeed(Request $request, $id)
    {
        $tags = app(ExploreService::class)->getTrendingTags();
        $tagMap = collect($tags)->keyBy('name');
        abort_if(! $tagMap->get($id), 404);
        $hashtagId = $tagMap->get($id)['id'];

        if ($request->user()) {
            $feed = VideoHashtag::whereHashtagId($hashtagId)
                ->whereVisibility(1)
                ->orderByDesc('id')
                ->cursorPaginate(10)
                ->withQueryString();
        } else {
            abort_if($request->has('cursor'), 404);
            $key = 'explore:getTagFeed:'.$hashtagId;
            $feed = Cache::remember($key, now()->addHours(12), function () use ($hashtagId) {
                return VideoHashtag::whereHashtagId($hashtagId)
                    ->whereVisibility(1)
                    ->orderByDesc('id')
                    ->cursorPaginate(15)
                    ->withQueryString();
            });
        }

        return VideoHashtagResource::collection($feed);
    }
}
