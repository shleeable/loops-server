<?php

namespace App\Http\Resources;

use App\Models\CommentReplyCaptionEdit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommentReplyCaptionEdit
 */
class CommentReplyCaptionEditResource extends JsonResource
{
    public function __construct(CommentReplyCaptionEdit $resource)
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
