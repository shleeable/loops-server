<?php

namespace App\Http\Resources;

use App\Models\Follower;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Follower
 */
class FollowerResource extends JsonResource
{
    public function __construct(Follower $resource)
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
        return AccountService::compact($this->profile_id);
    }
}
