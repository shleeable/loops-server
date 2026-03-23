<?php

namespace App\Http\Resources;

use App\Models\StarterKit;
use App\Services\AccountService;
use App\Services\StarterKitService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StarterKit
 */
class StarterKitResource extends JsonResource
{
    public function __construct(StarterKit $resource)
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
        $account = AccountService::compact($this->profile_id) ?? [];

        $user = $request->user();

        $url = $this->publicUrl();

        $accounts = app(StarterKitService::class)->accounts($this->id) ?? [];

        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $url,
            'path' => parse_url($url, PHP_URL_PATH),
            'remote_url' => $this->remote_url,
            'is_local' => $this->is_local,
            'is_discoverable' => $this->is_discoverable,
            'is_sensitive' => $this->is_sensitive,
            'created_at' => $this->created_at->format('c'),
            'updated_at' => $this->updated_at->format('c'),
            'is_owner' => (bool) $user && $user->profile_id == $this->profile_id,
            'creator' => $account,
            'uses' => $this->uses,
            'total_accounts' => $this->total_accounts,
            'approved_accounts' => $this->approved_accounts,
            'accounts' => $accounts,
            'header_url' => $this->header_url,
            'icon_url' => $this->icon_url,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'hashtags' => $this->hashtags->map(fn ($tag) => $tag->name),
        ];
    }
}
