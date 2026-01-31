<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoHashtagResource;
use App\Models\VideoHashtag;
use App\Services\AccountService;
use App\Services\ExploreService;
use App\Services\VideoService;
use App\Support\CursorToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExploreController extends Controller
{
    use ApiHelpers;

    public function getTrendingTags(Request $request)
    {
        return $this->data(app(ExploreService::class)->getTrendingTags());
    }

    public function getTagFeed(Request $request, $id)
    {
        $validated = $request->validate([
            'cursor' => 'sometimes|string|max:2000',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);
        $tags = app(ExploreService::class)->getTrendingTags();
        $tagMap = collect($tags)->keyBy('name');
        abort_if(! $tagMap->get($id), 404);
        $hashtagId = $tagMap->get($id)['id'];

        $preCursor = $validated['cursor'] ?? null;
        $limit = $validated['limit'] ?? 10;

        if ($request->user()) {
            $user = $request->user();
        } elseif ($request->bearerToken()) {
            $user = $request->user('api');
        } else {
            $user = null;
        }

        if ($user) {
            $ctx = hash('sha256', implode('|', [
                $user->id,
                'explore-tags',
                'hashtag:'.$hashtagId,
                'limit:'.$limit,
                'order:id_desc',
            ]));
            $decodedCursor = null;
            $hops = 0;

            $maxPages = $user->is_admin ? 100 : 10;
            $maxItems = $user->is_admin ? 300 : 120;

            if ($request->filled('cursor')) {
                ['cursor' => $decodedCursor, 'hops' => $hops] = CursorToken::decode($request->input('cursor'), $ctx);

                if ($hops >= $maxPages) {
                    return $this->defaultTagResponse($request, $limit);
                }

                if (($hops * $limit) >= $maxItems) {
                    return $this->defaultTagResponse($request, $limit);
                }
            }

            $pager = VideoHashtag::where('video_hashtags.hashtag_id', $hashtagId)
                ->where('video_hashtags.visibility', 1)
                ->join('videos', 'videos.id', '=', 'video_hashtags.video_id')
                ->join('profiles', 'profiles.id', '=', 'videos.profile_id')
                ->where('videos.status', 2)
                ->where('profiles.status', 1)
                ->select('video_hashtags.*')
                ->orderByDesc('video_hashtags.id')
                ->cursorPaginate(
                    perPage: $limit,
                    columns: ['*'],
                    cursorName: 'cursor',
                    cursor: $decodedCursor
                );

            $nextLaravelCursor = $pager->nextCursor()?->encode();
            $nextCursorToken = CursorToken::encode($nextLaravelCursor, $ctx, $hops + 1);

            $tags = $pager->getCollection();

            return VideoHashtagResource::collection($tags)->additional([
                'links' => [
                    'first' => null,
                    'last' => null,
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'path' => $request->url(),
                    'per_page' => $limit,
                    'next_cursor' => $nextCursorToken,
                    'prev_cursor' => $preCursor,
                ],
            ]);
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

            $filteredCollection = $feed->getCollection()->filter(function ($videoHashtag) {
                $videoData = app(VideoService::class)->getMediaData($videoHashtag->video_id);
                if (empty($videoData)) {
                    return false;
                }
                $profileId = data_get($videoData, 'account.id');
                if (! $profileId) {
                    return false;
                }
                $account = app(AccountService::class)->get($profileId);

                return $account;
            })->values();

            $feed->setCollection($filteredCollection);
        }

        return VideoHashtagResource::collection($feed);
    }

    public function defaultTagResponse($request, $limit)
    {
        return response()->json([
            'data' => [],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'path' => $request->url(),
                'per_page' => $limit,
                'next_cursor' => null,
                'prev_cursor' => null,
            ],
        ]);
    }
}
