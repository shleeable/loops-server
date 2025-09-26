<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

/**
 * @mixin Profile
 */
class ProfileResource extends JsonResource
{
    public function __construct(Profile $resource)
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
        $avatarUrl = $this->avatar ? str_starts_with($this->avatar, 'https://') ? $this->avatar : Storage::disk('s3')->url($this->avatar) : url('/storage/avatars/default.jpg');

        return [
            'id' => (string) $this->id,
            'name' => $this->name ?? 'user'.$this->id,
            'avatar' => $avatarUrl,
            'username' => $this->username,
            'is_owner' => false,
            'local' => (bool) $this->local,
            'bio' => $this->bio,
            'post_count' => $this->video_count,
            'follower_count' => $this->followers,
            'following_count' => $this->following,
            'url' => url('/@'.$this->username),
            'is_blocking' => null,
            'links' => $this->links ?? [],
            'created_at' => $this->created_at->format('c'),
        ];
    }
}
