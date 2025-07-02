<?php

namespace App\Http\Resources;

use App\Services\AccountService;
use App\Services\LikeService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class VideoResource extends JsonResource
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

        $pid = $this->getAuthenticatedProfileId($request);

        $res = [
            'id' => (string) $this->id,
            'account' => AccountService::compact($this->profile_id),
            'caption' => $this->caption,
            'url' => $this->shareUrl(),
            'is_owner' => $pid && $this->profile_id == $pid,
            'is_sensitive' => (bool) $this->is_sensitive,
            'media' => [
                'thumbnail' => $thumb,
                'src_url' => $mediaUrl,
                'hls_url' => null,
            ],
            'pinned' => $this->is_pinned,
            'likes' => $this->likes,
            'shares' => $this->shares,
            'comments' => $this->comments,
            'has_liked' => $pid ? LikeService::hasVideo($this->id, $pid) : false,
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
            'created_at' => $this->created_at->format('c'),
        ];

        if ($this->has_hls) {
            $res['media']['hls'] = $mediaUrl.'.m3u8';
        }

        return $res;
    }

    /**
     * Get the authenticated user's profile ID
     */
    private function getAuthenticatedProfileId(Request $request): ?int
    {
        return $request->user()?->profile_id;
    }
}
