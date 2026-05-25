<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\PlaylistVideoResource;
use App\Models\Playlist;
use Illuminate\Http\Request;

class VideoPlaylistController extends Controller
{
    use ApiHelpers;

    public function show(Request $request, $id)
    {
        $viewerId = optional($request->user())->profile;

        $playlist = Playlist::findOrFail($id);

        abort_if(! $playlist->isVisibleTo($viewerId), 403);

        if ($request->user()->cannot('view', $playlist)) {
            abort(403);
        }

        return new PlaylistResource($playlist);
    }

    public function videos(Request $request, $id)
    {
        $playlist = Playlist::findOrFail($id);

        $viewerId = optional($request->user())->profile;

        abort_if(! $playlist->isVisibleTo($viewerId), 403);

        if ($request->user()?->cannot('view', $playlist)) {
            abort(403);
        }

        $videos = $playlist->videos()
            ->select('videos.*', 'playlist_video.position')
            ->orderBy('playlist_video.position')
            ->orderBy('playlist_video.video_id')
            ->cursorPaginate(10)
            ->withQueryString();

        return PlaylistVideoResource::collection($videos);
    }
}
