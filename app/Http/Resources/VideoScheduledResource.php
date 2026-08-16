<?php

namespace App\Http\Resources;

use App\Models\Hashtag;
use App\Models\Video;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin Video
 */
class VideoScheduledResource extends JsonResource
{
    public function __construct(Video $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumb = url('/storage/videos/video-placeholder.jpg');
        if ($this->has_thumb) {
            if ($this->thumbnail) {
                $thumb = $this->thumbnail;
            } elseif ($this->thumbnail_path) {
                $thumb = Storage::disk('s3')->url($this->thumbnail_path);
            } else {
                $ext = pathinfo($this->vid, PATHINFO_EXTENSION);
                $url = str_replace('.'.$ext, '.jpg', $this->vid);
                $thumb = Storage::disk('s3')->url($url);
            }
        }

        $pid = $this->getAuthenticatedProfileId($request);
        $res = [
            'id' => (string) $this->id,
            'account' => AccountService::compact($this->profile_id),
            'caption' => $this->caption,
            'url' => null,
            'shortcode' => null,
            'is_owner' => $pid && (int) $this->profile_id === (int) $pid,
            'is_sensitive' => (bool) $this->is_sensitive,
            'is_local' => $this->is_local,
            'media' => [
                'thumbnail' => $thumb,
                'src_url' => null,
                'hls_url' => null,
                'alt_text' => $this->alt_text,
                'duration' => $this->duration,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'pinned' => $this->is_pinned,
            'likes' => 0,
            'shares' => 0,
            'comments' => $this->comment_state === 4 ? $this->comments : 0,
            'bookmarks' => 0,
            'has_liked' => false,
            'has_reposted' => false,
            'has_bookmarked' => false,
            'is_edited' => $this->is_edited,
            'lang' => $this->lang,
            'tags' => $this->hashtags->map(fn (Hashtag $tag) => $tag->name),
            'mentions' => $this->mentions,
            'permissions' => [
                'can_comment' => (bool) $this->comment_state == 4,
                'can_download' => (bool) $this->can_download,
                'can_duet' => (bool) $this->can_duet,
                'can_stitch' => (bool) $this->can_stitch,
                'can_embed' => (bool) $this->can_embed && ! $this->is_sensitive,
            ],
            'audio' => [
                'has_audio' => (bool) $this->has_audio,
                'id' => (string) 'at:'.$this->id,
                'count' => 0,
                'key' => (string) Str::uuid(),
                'sound_id' => $this->sound_id ? (string) $this->sound_id : null,
            ],
            'meta' => [
                'contains_ai' => $this->contains_ai,
                'contains_ad' => $this->contains_ad,
            ],
            'has_processed' => $this->status == 2 && $this->publish_state == Video::PUBLISH_STATE_PUBLISHED,
            'publish_state' => $this->publish_state,
            'scheduled_at' => $this->scheduled_at,
            'publish_failure_reason' => $this->publish_failure_reason,
            'created_at' => $this->created_at->format('c'),
        ];

        return $res;
    }

    /**
     * Get the authenticated user's profile ID
     */
    private function getAuthenticatedProfileId(Request $request): ?int
    {
        $pid = false;
        $user = auth('web')->user() ?? auth('api')->user();
        if ($user) {
            $pid = $user->profile_id;
        }

        return $pid;
    }
}
