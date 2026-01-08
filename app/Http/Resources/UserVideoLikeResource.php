<?php

namespace App\Http\Resources;

use App\Models\VideoLike;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VideoLike
 */
class UserVideoLikeResource extends JsonResource
{
    public function __construct(VideoLike $resource)
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
        if (! $this->video_id) {
            return [];
        }

        $res = VideoService::getMediaData($this->video_id);

        if (! $res) {
            return [];
        }

        unset($res['media']['src_url']);

        return $res;
    }
}
