<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentReplyResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowingResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\VideoResource;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Follower;
use App\Models\Page;
use App\Models\Profile;
use App\Models\Video;
use App\Services\AccountService;
use App\Services\FeedService;
use App\Services\ReportService;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebPublicController extends Controller
{
    use ApiHelpers;

    public function getFeed(Request $request)
    {
        $res = Cache::remember('wpc:get-feed', now()->addMinutes(45), function () {
            $request = new Request;
            $feed = FeedService::getPublicVideoFeed(20);

            $feed = collect($feed)->shuffle();

            return VideoResource::collection($feed->all())->toArray($request);
        });

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

    public function showVideo(Request $request, $id)
    {
        $video = Video::published()->find($id);

        if (! $video || ($request->user() && $request->user()->cannot('view', $video))) {
            return $this->error('Video not found or is unavailable', 404);
        }

        return new VideoResource($video);
    }

    /**
     * Get video comments.
     */
    public function comments(Request $request, $id)
    {
        $video = Video::published()->canComment()->find($id);

        if (! $video || ($request->user() && $request->user()->cannot('view', [Video::class, $video]))) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comments = Comment::withTrashed()
            ->whereVideoId($video->id)
            ->orderByDesc('id')
            ->cursorPaginate(10);

        return CommentResource::collection($comments);
    }

    /**
     * Get video comment replies.
     */
    public function commentsThread(Request $request, $id)
    {
        $this->validate($request, [
            'cr' => 'required|integer',
        ]);

        $video = Video::published()->canComment()->find($id);

        if (! $video || ($request->user() && $request->user()->cannot('view', [Video::class, $video]))) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comments = CommentReply::withTrashed()
            ->whereVideoId($video->id)
            ->whereCommentId($request->input('cr'))
            ->orderByDesc('id')
            ->cursorPaginate(3)
            ->withQueryString();

        return CommentReplyResource::collection($comments);
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
        $profile = Profile::findOrFail($id);

        if ($request->user() && $request->user()->cannot('viewAny', [Profile::class])) {
            return $this->error('Unavailable', 403);
        }

        $followers = $request->user() ?
            Follower::whereFollowingId($id)->orderByDesc('id')->cursorPaginate(15) :
            Follower::whereFollowingId($id)->orderBy('id')->limit(15)->get();

        return FollowerResource::collection($followers);
    }

    public function accountFollowing(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        if ($request->user() && $request->user()->cannot('viewAny', [Profile::class])) {
            return $this->error('Unavailable', 403);
        }

        $followers = $request->user() ?
            Follower::whereProfileId($id)->orderByDesc('id')->cursorPaginate(15) :
            Follower::whereProfileId($id)->orderBy('id')->limit(15)->get();

        return FollowingResource::collection($followers);
    }

    public function getAccountFeed(Request $request, $id)
    {
        if ($request->user() && $request->user()->cannot('viewAny', [Video::class])) {
            return $this->error('Please finish setting up your account', 403);
        }

        $request->validate([
            'sort' => 'sometimes|string|in:Latest,Popular,Oldest',
        ]);

        $sort = $request->input('sort', 'Latest');

        $profile = Profile::findOrFail($id);

        return FeedService::getAccountFeed($id, 10, $sort);
    }

    public function getContactInfo()
    {
        $keys = ['general.adminEmail', 'general.supportEmail', 'general.supportForum', 'general.supportFediverseAccount'];

        $keys = Cache::get('settings:admin');
        $contactInfo = [
            'admin_email' => $keys['settings']['general.adminEmail'],
            'support_email' => $keys['settings']['general.supportEmail'],
            'support_forum_url' => $keys['settings']['general.supportForum'],
            'fediverse_account' => $keys['settings']['general.supportFediverseAccount'],
        ];

        $contactInfo = array_filter($contactInfo, function ($value) {
            return ! is_null($value) && $value !== '';
        });

        return response()->json($contactInfo);
    }

    public function getPageContent(Request $request)
    {
        $this->validate($request, [
            'slug' => 'required|string|min:1|max:100',
        ]);

        $page = Page::whereSlug($request->slug)->published()->firstOrFail();
        $content = Str::of($page->content)->markdown();
        $res = [
            'title' => $page->title,
            'content' => $content,
            'slug' => $page->slug,
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at,
        ];

        return $this->data($res);
    }
}
