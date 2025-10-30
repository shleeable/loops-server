<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteVideoRequest;
use App\Http\Requests\GetMentionAutocomplete;
use App\Http\Requests\StoreCommentReplyUpdateRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreCommentUpdateRequest;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Resources\CommentCaptionEditResource;
use App\Http\Resources\CommentReplyCaptionEditResource;
use App\Http\Resources\CommentReplyResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\HashtagResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\VideoCaptionEditResource;
use App\Http\Resources\VideoResource;
use App\Jobs\Federation\DeliverCommentLikeActivity;
use App\Jobs\Federation\DeliverCommentReplyLikeActivity;
use App\Jobs\Federation\DeliverUndoCommentLikeActivity;
use App\Jobs\Federation\DeliverUndoCommentReplyLikeActivity;
use App\Jobs\Federation\DeliverUndoVideoLikeActivity;
use App\Jobs\Federation\DeliverVideoLikeActivity;
use App\Jobs\Video\VideoOptimizeJob;
use App\Jobs\Video\VideoThumbnailJob;
use App\Models\Comment;
use App\Models\CommentCaptionEdit;
use App\Models\CommentLike;
use App\Models\CommentReply;
use App\Models\CommentReplyCaptionEdit;
use App\Models\CommentReplyLike;
use App\Models\Hashtag;
use App\Models\Profile;
use App\Models\Video;
use App\Models\VideoCaptionEdit;
use App\Models\VideoLike;
use App\Services\AccountService;
use App\Services\ConfigService;
use App\Services\FederationDispatcher;
use App\Services\LikeService;
use App\Services\SanitizeService;
use App\Services\UserActivityService;
use App\Services\VideoService;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showAutocompleteTags(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:60']);

        $hashtags = Hashtag::where('name', 'like', $request->input('q').'%')->whereCanSearch(true)->limit(10)->get();

        return HashtagResource::collection($hashtags);
    }

    public function showAutocompleteAccounts(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:90']);

        $profiles = Profile::where('username', 'like', $request->input('q').'%')->where('is_hidden', false)->limit(10)->get();

        return ProfileResource::collection($profiles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoRequest $request)
    {
        $pid = $request->user()->profile_id;
        app(UserActivityService::class)->markActive($request->user());
        $profile = Profile::findOrFail($pid);
        $videoFile = $request->file('video');
        $videoMeta = [
            'size' => ceil($videoFile->getSize() / 1024),
            'name' => $videoFile->getClientOriginalName(),
            'mime' => $videoFile->getMimeType(),
        ];

        $model = new Video;
        $model->profile_id = $pid;
        $model->caption = app(SanitizeService::class)->cleanPlainText($request->description);
        $model->size_kb = intval($videoMeta['size']);
        $model->is_sensitive = $request->filled('is_sensitive') ? (bool) $request->boolean('is_sensitive') : false;
        $model->comment_state = $request->filled('comment_state') ? ($request->input('comment_state') == 4 ? 4 : 0) : 4;
        $model->can_download = $request->filled('can_download') ? $request->boolean('can_download') : false;
        // @phpstan-ignore-next-line
        $model->media_metadata = $videoMeta;
        $model->save();
        $path = $request->video->store('videos/'.$pid.'/'.$model->id, 's3');
        $model->vid = $path;
        $model->save();

        if ($request->filled('description')) {
            $model->syncHashtagsFromCaption();
            $model->syncMentionsFromCaption();
        }

        $profile->video_count = $profile->videos->count();
        $profile->save();

        $config = app(ConfigService::class);

        $batch = Bus::batch([
            [
                new VideoThumbnailJob($model),
            ],
            [
                new VideoOptimizeJob($model),
            ],
        ])->finally(function (Batch $batch) use ($model) {
            $config = app(ConfigService::class);
            if ($config->federation()) {
                app(FederationDispatcher::class)->dispatchVideoCreation($model);
            }
        })->dispatch();

        return $this->success();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoRequest $request, $id)
    {
        $pid = $request->user()->profile_id;
        $updatedCaption = $this->purifyText($request->caption);
        $video = Video::published()->findOrFail($id);
        if ($video->caption !== $updatedCaption) {
            VideoCaptionEdit::create([
                'video_id' => $video->id,
                'profile_id' => $video->profile_id,
                'caption' => $video->caption,
            ]);
        }
        $video->caption = $updatedCaption;
        $video->can_download = $request->has('can_download') ? $request->boolean('can_download') : false;
        $video->comment_state = $request->has('can_comment') ? ($request->boolean('can_comment') ? 4 : 0) : 0;
        $video->is_pinned = $request->has('is_pinned') ? $request->boolean('is_pinned') : 0;
        $video->pinned_order = $request->has('is_pinned') ? Video::whereStatus(2)->whereProfileId($pid)->whereIsPinned(true)->count() + 1 : 0;
        $video->is_edited = true;
        $video->save();
        $video->syncHashtagsFromCaption();
        $video->syncMentionsFromCaption();
        VideoService::deleteMediaData($video->id);
        $res = new VideoResource($video);
        $config = app(ConfigService::class);

        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchVideoUpdate($video);
        }

        return response()->json($res);
    }

    public function showVideoHistory(Request $request, $id)
    {
        $video = Video::published()->findOrFail($id);
        $paginator = VideoCaptionEdit::whereVideoId($video->id)->orderByDesc('id')->cursorPaginate(2)->withQueryString();
        if (! $request->has('cursor')) {
            $latest = new VideoCaptionEdit([
                'video_id' => (string) $video->id,
                'profile_id' => (string) $video->profile_id,
                'caption' => $video->caption,
                'updated_at' => $video->updated_at,
            ]);
            $collection = $paginator->getCollection();
            $collection->prepend($latest);
            $paginator->setCollection($collection->values());
        }

        return VideoCaptionEditResource::collection($paginator);
    }

    public function showCommentsHistory(Request $request, $vid, $cid)
    {
        $video = Video::published()->findOrFail($vid);
        $comment = Comment::whereVideoId($video->id)->findOrFail($cid);
        $paginator = CommentCaptionEdit::whereCommentId($comment->id)->orderByDesc('id')->cursorPaginate(2)->withQueryString();
        if (! $request->has('cursor')) {
            $latest = new CommentCaptionEdit([
                'comment_id' => (string) $comment->id,
                'profile_id' => (string) $comment->profile_id,
                'caption' => $comment->caption,
                'updated_at' => $comment->updated_at,
            ]);
            $collection = $paginator->getCollection();
            $collection->prepend($latest);
            $paginator->setCollection($collection->values());
        }

        return CommentCaptionEditResource::collection($paginator);
    }

    public function showCommentReplyHistory(Request $request, $vid, $cid, $id)
    {
        $video = Video::published()->findOrFail($vid);
        $comment = CommentReply::whereCommentId($cid)->findOrFail($id);
        $paginator = CommentReplyCaptionEdit::whereCommentId($comment->id)->orderByDesc('id')->cursorPaginate(2)->withQueryString();
        if (! $request->has('cursor')) {
            $latest = new CommentReplyCaptionEdit([
                'comment_id' => (string) $comment->id,
                'profile_id' => (string) $comment->profile_id,
                'caption' => $comment->caption,
                'updated_at' => $comment->updated_at,
            ]);
            $collection = $paginator->getCollection();
            $collection->prepend($latest);
            $paginator->setCollection($collection->values());
        }

        return CommentReplyCaptionEditResource::collection($paginator);
    }

    /**
     * Remove the specified resource from db/storage.
     */
    public function destroy(DeleteVideoRequest $request, $id)
    {
        $pid = $request->user()->profile_id;
        $video = Video::published()->findOrFail($id);
        VideoService::deleteMediaData($video->id);
        $videoId = $video->id;
        $videoObjectUrl = $video->getObjectUrl();
        $actor = $video->profile;

        if (str_starts_with($video->vid, 'https://')) {

        } else {
            if (Storage::exists($video->vid)) {
                Storage::delete($video->vid);
            }
            $s3Path = 'videos/'.$video->profile_id.'/'.$video->id.'/';
            if (Storage::disk('s3')->exists($s3Path)) {
                Storage::disk('s3')->deleteDirectory($s3Path);
            }
        }
        $config = app(ConfigService::class);

        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchVideoDeleteToAllKnownInboxes($actor, $videoId, $videoObjectUrl);
        }

        $video->forceDelete();
        VideoService::deleteMediaData($videoId);
        AccountService::del($pid);

        return $this->success();
    }

    /**
     * Like a video.
     */
    public function like(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        $video = Video::published()->find($id);

        if (! $video || $request->user()->cannot('create', [VideoLike::class, $video])) {
            return $this->error('Video not found or is unavailable', 404);
        }

        $like = VideoLike::firstOrCreate([
            'profile_id' => $pid,
            'video_id' => $video->id,
        ]);

        if ($like->wasRecentlyCreated) {
            $video->likes = VideoLike::whereVideoId($video->id)->count();
            $video->saveQuietly();

            app(LikeService::class)->addVideoLike((string) $video->id, (string) $pid);

            if ($video->uri) {
                $config = app(ConfigService::class);
                if ($config->federation()) {
                    DeliverVideoLikeActivity::dispatch($request->user()->profile, $video, $like)->onQueue('activitypub-out');
                }
            }
        }

        $resp = (new VideoResource($video))->toArray($request);
        $resp['has_liked'] = true;
        $resp['likes'] = $video->likes;

        return $resp;
    }

    /**
     * Unlike a video.
     */
    public function unlike(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        $video = Video::published()->find($id);

        if (! $video || $request->user()->cannot('create', [VideoLike::class, $video])) {
            return $this->error('Video not found or is unavailable', 404);
        }

        $res = VideoLike::where('profile_id', $pid)
            ->where('video_id', $video->id)
            ->first();

        if ($res) {
            if ($video->uri) {
                $config = app(ConfigService::class);
                if ($config->federation()) {
                    DeliverUndoVideoLikeActivity::dispatch(
                        $request->user()->profile,
                        $video,
                        $video->getObjectUrl(),
                        $res->id
                    )->onQueue('activitypub-out');
                }
            }
            app(LikeService::class)->removeVideoLike((string) $video->id, (string) $pid);
            $res->delete();
            $video->likes = VideoLike::whereVideoId($video->id)->count();
            $video->saveQuietly();
        } else {
            $resp = (new VideoResource($video))->toArray($request);

            return $resp;
        }

        $resp = (new VideoResource($video))->toArray($request);
        $resp['has_liked'] = false;
        $resp['likes'] = $video->likes;

        return $resp;
    }

    /**
     * Get video comments.
     */
    public function comments(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        $video = Video::published()->canComment()->find($id);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
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

        $pid = $request->user()->profile_id;
        $video = Video::published()->canComment()->find($id);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
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

    public function storeComment(StoreCommentRequest $request, $vid)
    {
        $pid = $request->user()->profile_id;
        $body = $this->purifyText($request->comment);

        $parentId = $request->filled('parent_id') ? $request->parent_id : false;
        $video = Video::published()->canComment()->find($vid);
        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        if ($parentId) {
            $parent = Comment::whereVideoId($vid)->findOrFail($parentId);
            $comment = new CommentReply;
            $comment->comment_id = $parentId;
            $comment->video_id = $vid;
            $comment->profile_id = $pid;
            $comment->caption = $body;
            $comment->save();
            $parent->increment('replies');
            $parent->saveQuietly();

            $comment->syncHashtagsFromCaption();
            $comment->syncMentionsFromCaption();

            $config = app(ConfigService::class);
            if ($config->federation()) {
                app(FederationDispatcher::class)->dispatchCommentReplyCreation($comment);
            }
        } else {
            $comment = new Comment;
            $comment->video_id = $vid;
            $comment->profile_id = $pid;
            $comment->caption = $body;
            $comment->save();

            $comment->syncHashtagsFromCaption();
            $comment->syncMentionsFromCaption();

            $config = app(ConfigService::class);
            if ($config->federation()) {
                app(FederationDispatcher::class)->dispatchCommentCreation($comment);
            }
        }

        $video->recalculateCommentsCount();

        return $parentId ?
            CommentReplyResource::collection([$comment]) :
            CommentResource::collection([$comment]);
    }

    public function storeCommentUpdate(StoreCommentUpdateRequest $request, $vid)
    {
        $pid = $request->user()->profile_id;
        $body = $this->purifyText($request->comment);

        $video = Video::published()->canComment()->find($vid);
        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = Comment::whereProfileId($pid)->findOrFail($request->id);
        CommentCaptionEdit::create([
            'comment_id' => $comment->id,
            'profile_id' => $pid,
            'caption' => $comment->caption,
        ]);

        $comment->update(['caption' => $body, 'is_edited' => true]);
        $comment->syncHashtagsFromCaption();
        $comment->syncMentionsFromCaption();

        $video->recalculateCommentsCount();

        $config = app(ConfigService::class);
        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchCommentUpdate($comment);
        }

        return CommentResource::collection([$comment]);
    }

    public function storeCommentReplyUpdate(StoreCommentReplyUpdateRequest $request, $vid)
    {
        $pid = $request->user()->profile_id;
        $body = $this->purifyText($request->comment);

        $video = Video::published()->canComment()->find($vid);
        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = CommentReply::whereProfileId($pid)->findOrFail($request->id);
        CommentReplyCaptionEdit::create([
            'comment_id' => $comment->id,
            'profile_id' => $pid,
            'caption' => $comment->caption,
        ]);

        $comment->update(['caption' => $body, 'is_edited' => true]);
        $comment->syncHashtagsFromCaption();
        $comment->syncMentionsFromCaption();

        $config = app(ConfigService::class);
        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchCommentReplyUpdate(
                $comment
            );
        }

        $video->recalculateCommentsCount();

        return CommentReplyResource::collection([$comment]);
    }

    public function deleteComment(Request $request, $vid, $id)
    {
        $video = Video::published()->findOrFail($vid);
        $comment = Comment::withCount('children')->findOrFail($id);
        $commentId = $comment->id;
        $commentObjectUrl = $comment->getObjectUrl();

        if ($comment->video_id !== $video->id || $comment->profile_id !== $request->user()->profile_id) {
            return $this->error('Record not found');
        }

        $config = app(ConfigService::class);
        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchCommentDeleteToAllKnownInboxes(
                $request->user()->profile,
                $commentId,
                $commentObjectUrl
            );
        }

        if ($comment->children_count) {
            $comment->update(['caption' => null, 'status' => 'deleted_by_user']);
            $comment->delete();
        } else {
            $comment->forceDelete();
        }

        $video->recalculateCommentsCount();

        return $this->success();
    }

    public function deleteCommentReply(Request $request, $vid, $pid, $id)
    {
        $video = Video::published()->findOrFail($vid);
        $parent = Comment::withTrashed()->withCount('children')->whereVideoId($vid)->findOrFail($pid);
        $comment = CommentReply::whereVideoId($video->id)
            ->whereProfileId($request->user()->profile_id)
            ->findOrFail($id);
        $commentId = $comment->id;
        $commentObjectUrl = $comment->getObjectUrl();

        $config = app(ConfigService::class);
        if ($config->federation()) {
            app(FederationDispatcher::class)->dispatchCommentReplyDeleteToAllKnownInboxes(
                $request->user()->profile,
                $commentId,
                $commentObjectUrl
            );
        }
        if ($parent->status != 'active' && $parent->children_count === 1) {
            $comment->forceDelete();
            $parent->forceDelete();
        } else {
            $parent->decrement('replies');
            $comment->forceDelete();
        }

        $video->recalculateCommentsCount();

        return $this->success();
    }

    public function storeCommentReplyLike(Request $request, $vid, $pid, $id)
    {
        $profileId = $request->user()->profile_id;

        $video = Video::published()->canComment()->find($vid);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = CommentReply::whereVideoId($vid)->whereCommentId($pid)->findOrFail($id);

        $commentLike = CommentReplyLike::updateOrCreate([
            'profile_id' => $profileId,
            'comment_id' => $comment->id,
        ]);

        if ($commentLike->wasRecentlyCreated) {
            $comment->increment('likes');
            $comment->saveQuietly();
            $video->increment('likes');
            $video->saveQuietly();
        }

        if ($comment->ap_id) {
            $config = app(ConfigService::class);
            if ($config->federation()) {
                DeliverCommentReplyLikeActivity::dispatch(
                    $request->user()->profile,
                    $comment->profile,
                    $commentLike,
                    $comment->getObjectUrl()
                )->onQueue('activitypub-out');
            }
        }

        return (new CommentReplyResource($comment))->toArray($request);
    }

    public function storeCommentReplyUnlike(Request $request, $vid, $pid, $id)
    {
        $profileId = $request->user()->profile_id;

        $video = Video::published()->canComment()->find($vid);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = CommentReply::whereVideoId($vid)->whereCommentId($pid)->findOrFail($id);

        $commentLike = CommentReplyLike::where([
            'profile_id' => $profileId,
            'comment_id' => $id,
        ])->first();

        if (! $commentLike) {
            return $this->success();
        }

        $commentLikeId = $commentLike->id;
        if ($comment->ap_id) {
            $config = app(ConfigService::class);
            if ($config->federation()) {
                DeliverUndoCommentReplyLikeActivity::dispatch(
                    $request->user()->profile,
                    $comment->profile,
                    $commentLikeId,
                    $comment->getObjectUrl()
                )->onQueue('activitypub-out');
            }
        }

        $commentLike->delete();
        if ($comment->likes) {
            $comment->decrement('likes');
            $comment->saveQuietly();
        }
        if ($video->likes) {
            $video->decrement('likes');
            $video->saveQuietly();
        }

        return (new CommentReplyResource($comment))->toArray($request);
    }

    public function storeCommentLike(Request $request, $vid, $id)
    {
        $pid = $request->user()->profile_id;

        $video = Video::published()->canComment()->find($vid);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = Comment::whereVideoId($vid)->findOrFail($id);

        $commentLike = CommentLike::updateOrCreate([
            'profile_id' => $pid,
            'comment_id' => $comment->id,
        ]);

        if ($comment->ap_id) {
            $config = app(ConfigService::class);
            if ($config->federation()) {
                $actorProfile = $request->user()->profile;
                $targetProfile = $comment->profile;
                $commentObjectUrl = $comment->getObjectUrl();
                DeliverCommentLikeActivity::dispatch(
                    $actorProfile,
                    $targetProfile,
                    $commentLike,
                    $commentObjectUrl
                )->onQueue('activitypub-out');
            }
        }

        if ($commentLike->wasRecentlyCreated) {
            $comment->increment('likes');
            $comment->saveQuietly();
            $video->increment('likes');
            $video->saveQuietly();
        }

        return (new CommentResource($comment))->toArray($request);
    }

    public function storeCommentUnlike(Request $request, $vid, $id)
    {
        $pid = $request->user()->profile_id;

        $video = Video::published()->canComment()->find($vid);

        if (! $video || $request->user()->cannot('view', [Video::class, $video])) {
            return $this->error('Video not found or is unavailable or has comments disabled', 404);
        }

        $comment = Comment::whereVideoId($vid)->findOrFail($id);

        $commentLike = CommentLike::where([
            'profile_id' => $pid,
            'comment_id' => $id,
        ])->first();

        if (! $commentLike) {
            return $this->success();
        }
        if ($comment->ap_id) {
            $commentObjectUrl = $comment->getObjectUrl();
            $commentId = $comment->id;
            $config = app(ConfigService::class);
            if ($config->federation()) {
                $actorProfile = $request->user()->profile;
                $targetProfile = $comment->profile;
                DeliverUndoCommentLikeActivity::dispatch(
                    $actorProfile,
                    $targetProfile,
                    $commentId,
                    $commentObjectUrl
                )->onQueue('activitypub-out');
            }
        }

        $commentLike->delete();
        if ($comment->likes) {
            $comment->decrement('likes');
            $comment->saveQuietly();
        }
        if ($video->likes) {
            $video->decrement('likes');
            $video->saveQuietly();
        }

        return (new CommentResource($comment))->toArray($request);
    }

    public function getAutocompleteMention(GetMentionAutocomplete $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 6);

        $res = Profile::where('username', 'like', $query.'%')->limit($limit)->get();

        return ProfileResource::collection($res);
    }

    public function getAutocompleteHashtag(GetMentionAutocomplete $request)
    {
        $query = $request->input('q');
        $limit = $request->input('limit', 6);

        $res = Hashtag::where('name', 'like', $query.'%')->limit($limit)->get();

        return HashtagResource::collection($res);
    }
}
