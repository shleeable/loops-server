<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudioVideoResource;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getPosts(Request $request)
    {
        $this->validate($request, [
            'search' => 'sometimes|nullable|alpha_dash|min:2|max:30',
            'limit' => 'sometimes|integer|min:1|max:10',
            'sort_field' => 'sometimes|string|in:created_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ]);

        $pid = $request->user()->profile_id;
        $search = $request->input('search');
        $limit = $request->input('limit');
        $sortBy = $request->input('sort_field');
        $sortDir = $request->input('sort_direction');

        $total = VideoService::totalUserCount($pid, false);
        $videos = Video::whereProfileId($pid)
            ->when($search, function ($query, $search) {
                $query->where('caption', 'like', '%'.$search.'%');
            })
            ->when($sortBy, function ($query, $sortBy) use ($sortDir) {
                $query->orderBy($sortBy, $sortDir);
            })
            ->cursorPaginate($limit)
            ->withQueryString();

        return StudioVideoResource::collection($videos)
            ->additional([
                'meta' => [
                    'total_videos' => $total,
                ],
            ]);
    }
}
