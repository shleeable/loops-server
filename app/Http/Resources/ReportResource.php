<?php

namespace App\Http\Resources;

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
            $contentPreview = null;
        }

        return [
            'id' => $this->id,
            'reporter' => AccountService::get($this->reporter_profile_id),
            'content_type' => $contentType,
            'content_preview' => $contentPreview,
            'admin_notes' => $this->admin_notes,
            'reason' => ReportService::getById($this->report_type),
            'status' => $this->admin_seen == 0 ? 'pending' : 'resolved',
            'created_at' => $this->created_at,
        ];
    }
}
