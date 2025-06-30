<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminHashtagResource extends JsonResource
{
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
