<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Services\FeedService;
use App\Services\UserActivityService;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function selfAccountFeed(Request $request)
    {
        if ($request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        return FeedService::getAccountFeed($request->user()->profile_id);
    }

    public function getForYouFeed(Request $request)
    {
        if ($request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }
        app(UserActivityService::class)->markActive($request->user());
        FeedService::enforcePaginationLimit($request);
        $feed = FeedService::getVideoFeed($request->user()->profile_id, 5);

        return VideoResource::collection($feed);
    }

    public function getFollowingFeed(Request $request)
    {
        if ($request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }
        FeedService::enforcePaginationLimit($request);

        $feed = Video::join('followers', 'videos.profile_id', '=', 'followers.following_id')
            ->where('followers.profile_id', $request->user()->profile_id)
            ->select('videos.*')
            ->orderBy('videos.id', 'desc')
            ->cursorPaginate(5)
            ->withQueryString();

        return VideoResource::collection($feed);
    }
}
