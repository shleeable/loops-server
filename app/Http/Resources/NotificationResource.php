<?php

namespace App\Http\Resources;

use App\Models\Notification;
use App\Services\AccountService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function __construct(Notification $resource)
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
        switch ($this->type) {
            case Notification::VIDEO_LIKE:
                return $this->newVideoLike();
                break;

            case Notification::NEW_FOLLOWER:
                return $this->newFollower();
                break;

            case Notification::NEW_VIDCOMMENT:
                return $this->newVideoComment();
                break;

            default:
                return [
                    'id' => (string) $this->id,
                    'type' => 'internal',
                    'read_at' => $this->read_at,
                    'created_at' => $this->created_at,
                ];
                break;
        }
    }

    protected function newVideoLike()
    {
        $video = VideoService::getMediaData($this->video_id);
        $thumb = data_get($video, 'media.thumbnail', null);

        return [
            'id' => (string) $this->id,
            'type' => 'video.like',
            'video_id' => (string) $this->video_id,
            'video_thumbnail' => $thumb,
            'actor' => AccountService::compact($this->profile_id),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }

    protected function newFollower()
    {
        return [
            'id' => (string) $this->id,
            'type' => 'new_follower',
            'actor' => AccountService::compact($this->profile_id),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }

    protected function newVideoComment()
    {
        $video = VideoService::getMediaData($this->video_id);
        $thumb = data_get($video, 'media.thumbnail', null);

        return [
            'id' => (string) $this->id,
            'type' => 'video.comment',
            'actor' => AccountService::compact($this->profile_id),
            'video_id' => (string) $this->video_id,
            'video_thumbnail' => $thumb,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
