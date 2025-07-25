<?php

namespace App\Http\Resources;

use App\Services\AccountService;
use App\Services\HashidService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StudioVideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = VideoService::getMediaData($this->id);

        $thumb = data_get($media, 'media.thumbnail', url('/videos/video-placeholder.jpg'));
        $mediaUrl = data_get($media, 'media.src_url', null);

        $status = match ($this->status) {
            1 => 'processing',
            2 => 'published',
            4 => 'archived',
            default => 'Pending'
        };

        $res = [
            'id' => (string) $this->id,
            'account' => AccountService::compact($this->profile_id),
            'caption' => $this->caption,
            'media' => [
                'thumbnail' => $thumb,
                'src_url' => $mediaUrl,
                'hls_url' => null,
            ],
            'status' => $status,
            'is_owner' => true,
            'likes' => $this->likes,
            'shares' => $this->shares,
            'comments' => $this->comments,
            'is_sensitive' => (bool) $this->is_sensitive,
            'created_at' => $this->created_at->format('c'),
        ];

        if ($this->status === 2) {
            $res = array_merge([
                'hid' => HashidService::encode($this->id),
                'url' => $this->shareUrl(),
                'pinned' => $this->is_pinned,
                'privacy' => 'everyone',
                'views_count' => 0,
                'has_liked' => null,
                'permissions' => [
                    'can_comment' => (bool) $this->comment_state == 4,
                    'can_download' => (bool) $this->can_download,
                    'can_duet' => (bool) $this->can_duet,
                    'can_stitch' => (bool) $this->can_stitch,
                ],
                'audio' => [
                    'has_audio' => (bool) $this->has_audio,
                    'id' => (string) 'at:'.$this->id,
                    'count' => 0,
                    'key' => (string) Str::uuid(),
                ],
            ], $res);
        }

        return $res;
    }
}
