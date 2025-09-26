<?php

namespace App\Http\Resources;

use App\Models\Hashtag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Hashtag
 */
class AdminHashtagResource extends JsonResource
{
    public function __construct(Hashtag $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->name_normalized,
            'can_search' => (bool) $this->can_search,
            'can_trend' => (bool) $this->can_trend,
            'can_autolink' => (bool) $this->can_autolink,
            'is_nsfw' => (bool) $this->is_nsfw,
            'is_banned' => (bool) $this->is_banned,
            'count' => $this->count,
            'created_at' => $this->created_at,
        ];
    }
}
