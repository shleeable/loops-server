<?php

namespace App\Http\Resources;

use App\Models\StarterKitAccount;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StarterKitAccount
 */
class StarterKitAccountResource extends JsonResource
{
    public function __construct(StarterKitAccount $resource)
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
        $account = AccountService::get($this->profile_id);
        $account['kit_status'] = $this->kit_status;
        $account['approved_at'] = $this->approved_at;
        $account['rejected_at'] = $this->rejected_at;
        $account['order'] = $this->order;
        $account['starter_kit_id'] = (string) $this->starter_kit_id;

        return $account;
    }
}
