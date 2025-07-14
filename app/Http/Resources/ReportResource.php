<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Services\AccountService;
use App\Services\ReportService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contentType = null;
        $contentPreview = null;

        if ($this->reported_profile_id) {
            $contentType = 'profile';
            $contentPreview = AccountService::get($this->reported_profile_id);
        } elseif ($this->reported_video_id) {
            $contentType = 'video';
            $contentPreview = VideoService::getMediaData($this->reported_video_id);
        } elseif ($this->reported_comment_id) {
            $contentType = 'comment';
            $comment = Comment::findOrFail($this->reported_comment_id);
            $contentPreview = new CommentResource($comment);
        } elseif ($this->reported_comment_reply_id) {
            $contentType = 'reply';
            $comment = CommentReply::with('parent')->findOrFail($this->reported_comment_reply_id);
            $contentPreview = (new CommentReplyResource($comment))->toArray(request());
            $contentPreview['parent'] = new CommentResource($comment->parent);
        }

        return [
            'id' => $this->id,
            'reporter' => AccountService::get($this->reporter_profile_id),
            'content_type' => $contentType,
            'content_preview' => $contentPreview,
            'admin_notes' => $this->admin_notes,
            'user_message' => $this->user_message,
            'is_remote' => (bool) $this->is_remote,
            'reason' => ReportService::getById($this->report_type),
            'status' => $this->admin_seen == 0 ? 'pending' : 'resolved',
            'created_at' => $this->created_at,
        ];
    }
}
