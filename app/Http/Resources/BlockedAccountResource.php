<?php

namespace App\Http\Resources;

use App\Models\UserFilter;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserFilter
 */
class BlockedAccountResource extends JsonResource
{
    public function __construct(UserFilter $resource)
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
            'account' => AccountService::compact($this->account_id),
            'blocked_at' => $this->created_at,
        ];
    }
}
