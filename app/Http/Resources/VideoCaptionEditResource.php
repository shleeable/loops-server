<?php

namespace App\Http\Resources;

use App\Models\VideoCaptionEdit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VideoCaptionEdit
 */
class VideoCaptionEditResource extends JsonResource
{
    public function __construct(VideoCaptionEdit $resource)
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
        return [
            'video_id' => (string) $this->video_id,
            'profile_id' => (string) $this->profile_id,
            'caption' => $this->caption,
            'updated_at' => $this->updated_at,
        ];
    }
}
