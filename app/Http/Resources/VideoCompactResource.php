<?php

namespace App\Http\Resources;

use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Video
 */
class VideoCompactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $res = VideoService::getMediaData($this->id);
        if ($res) {
            unset($res['media']['src_url']);
            unset($res['media']['width']);
            unset($res['media']['height']);
            unset($res['captionText']);
        }

        return $res;
    }
}
