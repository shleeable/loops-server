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

        $res = $this->searchLocal($cleanQuery, $currentUserId);

        if ($res->isEmpty() && $this->isRemoteQuery($cleanQuery)) {
            $remoteProfile = $this->searchRemote($cleanQuery, $currentUserId);

            if ($remoteProfile) {
                return ProfileResource::collection(collect([$remoteProfile]));
            }
        }

        return ProfileResource::collection($res);
    }

    protected function searchLocal(string $cleanQuery, int $currentUserId)
    {
        $escapedQuery = str_replace(['%', '_'], ['\%', '\_'], $cleanQuery);

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
            ->where('profiles.id', '!=', $currentUserId)
            ->whereNotExists(function ($query) use ($currentUserId) {
                $query->select('id')
                    ->from('user_filters')
                    ->whereColumn('user_filters.account_id', 'profiles.id')
                    ->where('user_filters.profile_id', $currentUserId);
            })
            ->where('profiles.username', 'like', $escapedQuery.'%')
            ->where('profiles.status', 1)
            ->groupBy('profiles.id', 'profiles.username', 'profiles.followers')
            ->orderByDesc('is_followed')
            ->orderByDesc('profiles.followers')
            ->cursorPaginate(10)
            ->withQueryString();
    }

    protected function searchRemote(string $query, int $currentUserId): ?Profile
    {
        try {
            if (filter_var($query, FILTER_VALIDATE_URL)) {
                return $this->lookupByUrl($query, $currentUserId);
            }

            $profile = $this->webfingerService->findOrCreateRemoteActor($query);

            if (! $profile || $this->isBlocked($profile->id, $currentUserId)) {
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

    protected function lookupByUrl(string $url, int $currentUserId): ?Profile
    {
        $existing = Profile::where('uri', $url)->first();

        if ($existing) {
            return $this->isBlocked($existing->id, $currentUserId) ? null : $existing;
        }

        return Profile::findOrCreateFromUrl($url, null, true);
    }

    protected function isBlocked(int $profileId, int $currentUserId): bool
    {
        return UserFilter::where('profile_id', $currentUserId)
            ->where('account_id', $profileId)
            ->exists();
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
