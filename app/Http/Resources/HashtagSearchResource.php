<?php

namespace App\Http\Resources;

use App\Models\Hashtag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\CursorPaginator;

/**
 * @mixin Hashtag
 */
class HashtagSearchResource extends ResourceCollection
{
    public $collects = HashtagResource::class;

    public function __construct(CursorPaginator $resource)
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
            'data' => [
                'hashtags' => HashtagResource::collection($this->collection),
                'users' => [],
                'videos' => [],
            ],
        ];
    }
}
