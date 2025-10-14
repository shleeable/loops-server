<?php

namespace App\Http\Resources;

use App\Models\VideoHashtag;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VideoHashtag
 */
class VideoHashtagResource extends JsonResource
{
    public function __construct(VideoHashtag $resource)
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
        $res = VideoService::getMediaData($this->video_id);
        if ($res) {
            unset($res['media']['src_url']);
        }

        return $res;
    }
}
