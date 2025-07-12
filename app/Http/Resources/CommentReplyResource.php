<?php

namespace App\Http\Resources;

use App\Services\AccountService;
use App\Services\LikeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pid = $request->user() ? $request->user()->profile_id : false;

        $res = [
            'id' => (string) $this->id,
            'v_id' => (string) $this->video_id,
            'account' => AccountService::compact($this->profile_id),
            'caption' => $this->caption,
            'likes' => $this->likes ?? 0,
            'liked' => $pid ? LikeService::hasCommentReplyLike($this->comment_id, $this->id, $pid) : false,
            'url' => $this->shareUrl(),
            'is_owner' => $pid ? (string) $this->profile_id === (string) $pid : false,
            'created_at' => $this->created_at->format('c'),
        ];

        return $res;
    }
}
