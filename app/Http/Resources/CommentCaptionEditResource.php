<?php

namespace App\Http\Resources;

use App\Models\CommentCaptionEdit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommentCaptionEdit
 */
class CommentCaptionEditResource extends JsonResource
{
    public function __construct(CommentCaptionEdit $resource)
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
            'comment_id' => (string) $this->comment_id,
            'profile_id' => (string) $this->profile_id,
            'caption' => $this->caption,
            'updated_at' => $this->updated_at,
        ];
    }
}
