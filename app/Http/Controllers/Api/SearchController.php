<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Models\UserFilter;
use App\Services\WebfingerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    use ApiHelpers;

    protected WebfingerService $webfingerService;

    public function __construct(WebfingerService $webfingerService)
    {
        $this->middleware('auth');
        $this->webfingerService = $webfingerService;
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

        $res = $this->searchLocal($cleanQuery, $blocked, $currentUserId);

        if ($res->isEmpty() && $this->isRemoteQuery($cleanQuery)) {
            $remoteProfile = $this->searchRemote($cleanQuery, $currentUserId, $blocked);

            if ($remoteProfile) {
                // Convert single profile to collection for consistent response format
                return ProfileResource::collection(collect([$remoteProfile]));
            }
        }

        return ProfileResource::collection($res);
    }

    /**
     * Search local profiles
     */
    protected function searchLocal(string $cleanQuery, array $blocked, int $currentUserId)
    {
        return Profile::select([
            'profiles.id',
            'profiles.local',
            'profiles.name',
            'profiles.avatar',
            'profiles.username',
            'profiles.following',
            'profiles.followers',
            'profiles.video_count',
            'profiles.domain',
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
    }

    /**
     * Search for remote profile via webfinger
     */
    protected function searchRemote(string $query, int $currentUserId, array $blocked): ?Profile
    {
        try {
            if (filter_var($query, FILTER_VALIDATE_URL)) {
                return $this->lookupByUrl($query, $blocked);
            }

            $profile = $this->webfingerService->findOrCreateRemoteActor($query);

            if (! $profile || in_array($profile->id, $blocked)) {
                return null;
            }

            return $profile;
        } catch (\Exception $e) {
            Log::warning('Remote profile lookup failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Lookup a profile by ActivityPub URL
     */
    protected function lookupByUrl(string $url, array $blocked): ?Profile
    {
        $existing = Profile::where('uri', $url)->first();

        if ($existing) {
            return in_array($existing->id, $blocked) ? null : $existing;
        }

        return Profile::findOrCreateFromUrl($url, null, true);
    }

    /**
     * Check if query looks like a remote mention
     */
    protected function isRemoteQuery(string $query): bool
    {
        if (preg_match('/^@?[a-zA-Z0-9_]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $query)) {
            return true;
        }

        if (filter_var($query, FILTER_VALIDATE_URL)) {
            return true;
        }

        return false;
    }
}
