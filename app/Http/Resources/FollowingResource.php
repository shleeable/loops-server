<?php

namespace App\Http\Resources;

use App\Models\Follower;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Follower
 */
class FollowingResource extends JsonResource
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
        $account = AccountService::compact($this->following_id);

        if (isset($this->is_following)) {
            $account['is_following'] = (bool) $this->is_following;
        }

        return $account;
    }
}
