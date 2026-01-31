<?php

namespace App\Http\Controllers;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoBookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function bookmarks(Request $request)
    {
        $validated = $request->validate([
            'sort' => 'sometimes|in:latest,oldest,popular',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $limit = data_get($validated, 'limit', 12);
        $cursor = data_get($validated, 'cursor', null);
        $profileId = $request->user()->profile_id;

        $posts = Video::addSelect([
            'videos.*',
            'videos.status',
            'video_bookmarks.created_at',
            'video_bookmarks.video_id',
            DB::raw('1 as is_bookmarked'),
        ])
            ->join('video_bookmarks', 'videos.id', '=', 'video_bookmarks.video_id')
            ->join('profiles', 'profiles.id', '=', 'videos.profile_id')
            ->where('video_bookmarks.profile_id', $profileId)
            ->where('videos.status', 2)
            ->where('profiles.status', 1)
            ->orderBy('video_bookmarks.created_at', 'desc')
            ->orderBy('video_bookmarks.id', 'desc')
            ->cursorPaginate($limit, ['*'], 'cursor', $cursor)
            ->withQueryString();

        return VideoResource::collection($posts);
    }
}
