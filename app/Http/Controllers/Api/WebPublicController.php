<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowingResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\VideoResource;
use App\Models\Follower;
use App\Models\Profile;
use App\Models\Video;
use App\Services\AccountService;
use App\Services\FeedService;
use App\Services\ReportService;
use Illuminate\Http\Request;

class WebPublicController extends Controller
{
    use ApiHelpers;

    public function getFeed(Request $request)
    {
        $feed = FeedService::getPublicVideoFeed();

        $res = VideoResource::collection($feed)->toArray($request);

        return response()->json([
            'data' => $res,
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'path' => $request->url(),
                'per_page' => count($res),
                'next_cursor' => null,
                'prev_cursor' => null,
            ],
        ]);
    }

    public function reportTypes()
    {
        return response()->json(ReportService::getRules());
    }

    public function getAccountInfoByUsername(Request $request, $id)
    {
        if ($request->user() && $request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        $profile = Profile::whereUsername($id)->firstOrFail();
        $res = (new ProfileResource($profile))->toArray($request);
        $res['is_owner'] = false;
        $res['likes_count'] = AccountService::getAccountLikesCount($profile->id);

        return response()->json(['data' => $res]);
    }

    public function accountFollowers(Request $request, $id)
    {
        if ($request->user() && $request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        $profile = Profile::findOrFail($id);
        $followers = Follower::whereFollowingId($id)->orderByDesc('id')->cursorPaginate(15);

        return FollowerResource::collection($followers);
    }

    public function accountFollowing(Request $request, $id)
    {
        if ($request->user() && $request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        $profile = Profile::findOrFail($id);
        $followers = Follower::whereProfileId($id)->orderByDesc('id')->cursorPaginate(15);

        return FollowingResource::collection($followers);
    }

    public function getAccountFeed(Request $request, $id)
    {
        if ($request->user() && $request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        $profile = Profile::findOrFail($id);

        return FeedService::getAccountFeed($id);
    }
}
