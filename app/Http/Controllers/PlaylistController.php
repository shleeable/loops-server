<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Requests\CreatePlaylistRequest;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\PlaylistVideoResource;
use App\Models\Playlist;
use App\Models\Video;
use App\Services\PlaylistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlaylistController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth:web,api');
    }

    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        $query = Playlist::where('profile_id', $profile->id);

        if ($request->filled('search')) {
            $preSearch = trim($request->input('search'));
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $preSearch);
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        $allowed = ['created_at', 'order_column', 'name'];
        $sortField = $request->input('sort_field', 'order_column');
        $sortDirection = $request->input('sort_direction', 'asc');

        $query->orderBy(
            in_array($sortField, $allowed) ? $sortField : 'order_column',
            $sortDirection === 'asc' ? 'asc' : 'desc'
        );

        $perPage = $request->input('limit', 10);
        $playlists = $query->cursorPaginate($perPage);

        return PlaylistResource::collection($playlists);
    }

    public function store(CreatePlaylistRequest $request)
    {
        $validated = $request->validated();

        $playlist = $request->user()->profile->playlists()->create($validated);

        return new PlaylistResource($playlist);
    }

    public function show(Request $request, Playlist $playlist)
    {
        $pid = $request->user()->profile_id;

        if ((int) $playlist->profile_id !== (int) $pid) {
            abort(403, 'You do not have permission to view this playlist.');
        }

        return new PlaylistResource($playlist);
    }

    public function videos(Request $request, Playlist $playlist)
    {
        $request->validate([
            'limit' => 'sometimes|int|min:1|max:10',
        ]);

        $viewer = $request->user()?->profile;

        if (! $playlist->isVisibleTo($viewer)) {
            abort(403, 'You do not have permission to view this playlist.');
        }

        $perPage = $request->input('limit', 10);

        $videos = $playlist->videos()
            ->select('videos.*', 'playlist_video.position')
            ->orderBy('playlist_video.position')
            ->orderBy('playlist_video.video_id')
            ->cursorPaginate($perPage)
            ->withQueryString();

        return PlaylistVideoResource::collection($videos);
    }

    public function update(Request $request, Playlist $playlist)
    {
        Gate::authorize('update', $playlist);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:30',
            'description' => 'nullable|string|max:1000',
            'visibility' => 'sometimes|in:public,unlisted,private,followers',
        ]);

        $playlist->update($validated);
        $playlist->updateProfileHasPlaylists();

        return new PlaylistResource($playlist);
    }

    public function destroy(Request $request, Playlist $playlist)
    {
        Gate::authorize('delete', $playlist);

        $playlist->delete();

        $profile = $request->user()->profile;

        $exists = Playlist::whereProfileId($profile->id)
            ->where('visibility', 'public')
            ->has('videos', '>', 1)
            ->exists();

        $profile->update(['has_playlists' => $exists]);

        return response()->json(null, 204);
    }

    public function addVideo(Request $request, Playlist $playlist)
    {
        Gate::authorize('update', $playlist);

        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
            'position' => 'nullable|integer|min:0',
        ]);

        $video = Video::published()->findOrFail($validated['video_id']);

        if ($video->profile_id != $playlist->profile_id) {
            abort(403, 'You can only add your own videos to playlists.');
        }

        $playlist->addVideo($video, $validated['position'] ?? null);

        $playlist->update(['videos_count' => $playlist->videos()->count()]);

        return response()->json(['message' => 'Video added to playlist']);
    }

    public function removeVideo(Playlist $playlist, Video $video)
    {
        Gate::authorize('update', $playlist);

        $playlist->removeVideo($video);

        $playlist->update(['videos_count' => $playlist->videos()->count()]);

        return response()->json(['message' => 'Video removed from playlist']);
    }

    public function reorder(Request $request, Playlist $playlist)
    {
        Gate::authorize('update', $playlist);

        $validated = $request->validate([
            'video_ids' => 'required|array',
            'video_ids.*' => 'exists:videos,id',
        ]);

        $playlist->reorderVideos($validated['video_ids']);

        return response()->json(['message' => 'Playlist reordered']);
    }

    public function reorderPlaylistOrder(Request $request)
    {
        $request->validate([
            'playlist_ids' => 'required|array',
            'playlist_ids.*' => 'required|integer',
        ]);

        $profile = $request->user()->profile;

        $count = Playlist::where('profile_id', $profile->id)
            ->whereIn('id', $request->input('playlist_ids'))
            ->count();

        if ($count !== count($request->input('playlist_ids'))) {
            abort(403, 'Invalid playlist IDs');
        }

        Playlist::reorder($profile->id, $request->input('playlist_ids'));

        return response()->json(['message' => 'Playlists reordered']);
    }

    public function getLimits(Request $request)
    {
        $profile = $request->user()->profile;

        return $this->data(PlaylistService::getDetails($profile));
    }
}
