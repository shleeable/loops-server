<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Models\UserFilter;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get(SearchRequest $request)
    {
        $q = $request->input('q');
        $cleanQuery = Str::of($q)->startsWith('@') ? Str::substr($q, 1) : $q;

        $currentUserId = $request->user()->profile_id;
        $blocked = UserFilter::whereProfileId($currentUserId)
            ->pluck('account_id')
            ->toArray();

        $blocked[] = $currentUserId;

        $res = Profile::select([
            'profiles.id',
            'profiles.name',
            'profiles.avatar',
            'profiles.username',
            'profiles.following',
            'profiles.followers',
            'profiles.video_count',
            'profiles.status',
            'profiles.created_at',
        ])
            ->selectRaw('MAX(CASE WHEN followers.following_id IS NOT NULL THEN 1 ELSE 0 END) as is_followed')
            ->leftJoin('followers', function ($join) use ($currentUserId) {
                $join->on('followers.following_id', '=', 'profiles.id')
                    ->where('followers.profile_id', '=', $currentUserId);
            })
            ->whereNotIn('profiles.id', $blocked)
            ->where(function ($query) use ($cleanQuery) {
                $query->where('profiles.username', 'like', $cleanQuery.'%');
            })
            ->groupBy('profiles.id', 'profiles.username', 'profiles.followers')
            ->whereStatus(1)
            ->orderByDesc('is_followed')
            ->orderByDesc('profiles.followers')
            ->cursorPaginate(10)
            ->withQueryString();

        return ProfileResource::collection($res);
    }
}
