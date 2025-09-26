<?php

namespace App\Http\Resources;

use App\Models\Hashtag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Hashtag
 */
class HashtagResource extends JsonResource
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
            'count' => $this->count,
            'created_at' => $this->created_at,
        ];
    }
}
