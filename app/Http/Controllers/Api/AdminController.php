<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminHashtagResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\ReportResource;
use App\Http\Resources\VideoResource;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Hashtag;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
use App\Services\AccountService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function videos(Request $request)
    {
        $search = $request->query('q');
        $sort = $request->query('sort');

        $query = Video::when($search, function ($query, $search) {
            $query->join('profiles', 'videos.profile_id', '=', 'profiles.id')
                ->where('profiles.username', 'like', '%'.$search.'%')
                ->select('videos.*');
        });

        $query = $this->applySorting($query, $sort);
        $videos = $query->orderByDesc('id')->cursorPaginate(10)->withQueryString();

        return VideoResource::collection($videos);
    }

    public function videoShow(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $res = (new VideoResource($video))->toArray($request);
        $res['status'] = $video->statusLabel();
        $res['media']['size'] = $video->size_kb;
        $res['hid'] = $video->hashid();
        $res['reported_count'] = Report::whereReportedVideoId($id)->count();

        return $this->data($res);
    }

    public function videoCommentsShow(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $comments = Comment::whereVideoId($video->id)
            ->orderByDesc('id')
            ->cursorPaginate(5);

        return CommentResource::collection($comments);
    }

    public function videoModerate(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:unpublished,publish',
        ]);

        $action = $request->input('action');
        $video = Video::findOrFail($id);
        $video->status = $action == 'unpublished' ? 6 : 2;
        $video->saveQuietly();
        VideoService::getMediaData($video->id, true);

        $res = (new VideoResource($video))->toArray($request);
        $res['status'] = $video->statusLabel();
        $res['media']['size'] = $video->size_kb;
        $res['hid'] = $video->hashid();

        return $this->data($res);
    }

    public function videoCommentsDelete(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $video = Video::findOrFail($comment->video_id);
        $comment->delete();
        $video->decrement('comments');

        return $this->success();
    }

    public function profiles(Request $request)
    {
        $search = $request->query('q');
        $sort = $request->query('sort');

        $query = Profile::query();

        if (! empty($search)) {
            if (str_starts_with($search, 'bio:')) {
                $bio = trim(substr($search, 4));
                if (! empty($bio)) {
                    $query->where('bio', 'like', '%'.$bio.'%');
                }
            } elseif (str_starts_with($search, 'name:')) {
                $name = trim(substr($search, 5));
                if (! empty($name)) {
                    $query->where('name', 'like', '%'.$name.'%');
                }
            } else {
                $query->where('username', 'like', '%'.$search.'%');
            }
        }

        $query = $this->applySorting($query, $sort);

        $profiles = $query->cursorPaginate(10)->withQueryString();

        return ProfileResource::collection($profiles);
    }

    public function profileShow(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        $res = (new ProfileResource($profile))->toArray($request);

        if ($profile->local) {
            $user = $profile->user;
            $res['is_admin'] = (bool) $user->is_admin;
            $res['email'] = $user->email;
            $res['email_verified'] = (bool) $user->email_verified_at;
            $res['can_upload'] = (bool) $user->can_upload;
            $res['can_comment'] = (bool) $user->can_comment;
            $res['can_follow'] = (bool) $user->can_follow;
            $res['can_like'] = (bool) $user->can_like;
            $res['admin_note'] = $user->admin_note;
        }
        $res['comments_count'] = Comment::whereProfileId($profile->id)->count();
        $res['comment_replies_count'] = CommentReply::whereProfileId($profile->id)->count();
        $res['reports_created_count'] = Report::whereReporterProfileId($profile->id)->count();
        $res['reported_count'] = Report::totalReportsAgainstProfile($profile->id);
        $res['likes_count'] = AccountService::getAccountLikesCount($profile->id);
        $res['status'] = $profile->status == 1 ? 'active' : 'suspended';

        return $this->data($res);
    }

    public function profilePermissionUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'can_upload' => 'sometimes|boolean',
            'can_follow' => 'sometimes|boolean',
            'can_comment' => 'sometimes|boolean',
            'can_like' => 'sometimes|boolean',
        ]);

        $profile = Profile::findOrFail($id);

        if (! $profile->local) {
            return $this->error('Ooops!');
        }

        $user = $profile->user;

        $user->update($validated);

        return $this->success();
    }

    public function profileAdminNoteUpdate(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'sometimes|nullable|string',
        ]);

        $profile = User::whereProfileId($id)->first();

        if (! $profile) {
            return $this->error('Ooops!');
        }

        $profile->admin_note = $request->input('admin_note');
        $profile->save();

        return $this->success();
    }

    public function reports(Request $request)
    {
        $search = $request->query('q');
        $sort = $request->query('sort', 'open');

        if ($request->filled('q') && ! $request->has('sort')) {
            $sort = 'all';
        }

        $reports = Report::search($search)
            ->filterByStatus($sort)
            ->paginated()
            ->cursorPaginate(10)
            ->withQueryString();

        return ReportResource::collection($reports);
    }

    public function reportShow(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        return new ReportResource($report);
    }

    public function reportUpdateMarkAsNsfw(Request $request, $id)
    {
        $report = Report::whereNotNull('reported_video_id')->whereAdminSeen(false)->findOrFail($id);
        $video = $report->video;
        $video->is_sensitive = true;
        $video->save();
        $report->admin_seen = true;
        $report->save();

        return $this->success();
    }

    public function reportDismiss(Request $request, $id)
    {
        $report = Report::whereAdminSeen(false)->findOrFail($id);
        $report->admin_seen = true;
        $report->save();

        return $this->success();
    }

    public function reportDismissAllByAccount(Request $request, $id)
    {
        $report = Report::whereAdminSeen(false)->findOrFail($id);

        Report::whereReporterProfileId($report->reporter_profile_id)
            ->update([
                'handled' => true,
                'admin_seen' => true,
            ]);

        return $this->success();
    }

    public function reportDeleteVideo(Request $request, $id)
    {
        $report = Report::whereNotNull('reported_video_id')->whereAdminSeen(false)->findOrFail($id);
        $videoId = $report->reported_video_id;
        $report->delete();

        $video = Video::published()->findOrFail($videoId);
        $pid = $video->profile_id;
        VideoService::deleteMediaData($video->id);

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
        $video->forceDelete();

        AccountService::del($pid);

    }

    public function reportDeleteComment(Request $request, $id)
    {
        $report = Report::whereNotNull('reported_comment_id')->whereAdminSeen(false)->findOrFail($id);
        $comment = Comment::withCount('children')->findOrFail($report->reported_comment_id);
        $report->admin_seen = true;
        $report->save();
        $vid = $comment->video_id;
        if ($comment->children_count == 0) {
            $comment->forceDelete();
        } else {
            $comment->status = 'deleted_by_admin';
            $comment->delete();
            $report->admin_seen = true;
            $report->save();
        }
        if ($vid) {
            Video::findOrFail($vid)->recalculateCommentsCount();
        }

        return $this->success();
    }

    public function reportDeleteCommentReply(Request $request, $id)
    {
        $report = Report::whereNotNull('reported_comment_reply_id')->whereAdminSeen(false)->findOrFail($id);
        $commentReply = CommentReply::with('parent')->findOrFail($report->reported_comment_reply_id);
        $vid = $commentReply->video_id;
        if ($commentReply->parent) {
            $commentReply->parent->decrement('replies');
        } else {
            $parent = Comment::withTrashed()->findOrFail($commentReply->comment_id);
            $parent->forceDelete();
        }
        $commentReply->forceDelete();
        if ($vid) {
            Video::findOrFail($vid)->recalculateCommentsCount();
        }

        return $this->success();
    }

    public function reportUpdateAdminNotes(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|nullable|max:500',
        ]);
        $report = Report::findOrFail($id);
        $report->admin_notes = $request->input('content');
        $report->save();

        return new ReportResource($report);
    }

    public function comments(Request $request)
    {
        $query = Comment::query();

        $search = $request->get('q');

        if (! empty($search)) {
            if (str_starts_with($search, 'user:')) {
                $username = trim(substr($search, 5));
                if (! empty($username)) {
                    $query->join('profiles', 'comments.profile_id', '=', 'profiles.id')
                        ->where('profiles.username', 'like', '%'.$username.'%')
                        ->select('comments.*');
                }
            } elseif (str_starts_with($search, 'video:')) {
                $videoId = trim(substr($search, 6));
                if (! empty($videoId)) {
                    $query->where('video_id', $videoId);
                }
            } else {
                $query->where('caption', 'like', '%'.$search.'%');
            }
        }

        $comments = $query->orderByDesc('id')
            ->cursorPaginate(10)
            ->withQueryString();

        return CommentResource::collection($comments);
    }

    public function hashtags(Request $request)
    {
        $q = $request->query('q');
        $sort = $request->query('sort');

        $query = Hashtag::when($q, function ($query, $q) {
            $query->where('name', 'like', $q.'%')->orderByDesc('count');
        });

        $query = $this->applySorting($query, $sort);

        $tags = $query->cursorPaginate(10)->withQueryString();

        return AdminHashtagResource::collection($tags);
    }

    private function applySorting($query, $sort)
    {
        $sortOptions = [
            'username_asc' => ['username', 'asc'],
            'username_desc' => ['username', 'desc'],
            'video_count_desc' => ['video_count', 'desc'],
            'followers_asc' => ['followers', 'asc'],
            'followers_desc' => ['followers', 'desc'],
            'likes_desc' => ['likes', 'desc'],
            'comments_desc' => ['comments', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
            'created_at_desc' => ['created_at', 'desc'],
            'updated_at_asc' => ['updated_at', 'asc'],
            'updated_at_desc' => ['updated_at', 'desc'],
            'popular' => ['followers', 'desc'],
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'count_desc' => ['count', 'desc'],
        ];

        if ($sort && isset($sortOptions[$sort])) {
            [$column, $direction] = $sortOptions[$sort];
            $query->orderBy($column, $direction);

            if ($column !== 'id') {
                $query->orderBy('id', 'desc');
            }
        } else {
            if (request()->query('q')) {
                $query->orderBy('followers', 'desc')->orderBy('id', 'desc');
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        return $query;
    }
}
