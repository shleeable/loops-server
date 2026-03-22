<?php

namespace App\Http\Resources;

use App\Models\StarterKitPendingChange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StarterKitPendingChange
 */
class StarterKitPendingChangeResource extends JsonResource
{
    public function __construct(StarterKitPendingChange $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'applied_at' => $this->applied_at,
            'original' => $this->original,
            'changes' => $this->changeset,
            'starter_kit' => [
                'id' => (string) $this->starterKit->id,
                'title' => $this->starterKit->title,
                'description' => $this->starterKit->description,
                'icon_url' => $this->starterKit->icon_url,
                'header_url' => $this->whenLoaded('starterKit', fn () => $this->starterKit->header_url),
            ],
            'profile' => [
                'id' => $this->profile->id,
                'username' => $this->profile->username,
                'avatar' => $this->profile->avatar,
            ],
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id' => $this->reviewer->id,
                'username' => $this->reviewer->username,
            ] : null),
        ];
    }
}
