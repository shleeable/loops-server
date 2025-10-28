<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowingResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\ProfileResource;
use App\Jobs\Federation\DeliverFollowRequest;
use App\Jobs\Federation\DeliverUndoFollowActivity;
use App\Models\Follower;
use App\Models\FollowRequest;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\UserFilter;
use App\Services\AccountService;
use App\Services\FollowerService;
use App\Services\NotificationService;
use App\Services\UserFilterService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function selfAccountInfo(Request $request)
    {
        $user = $request->user();
        $pid = $user->profile_id;
        $profile = Profile::findOrFail($pid);
        $res = (new ProfileResource($profile))->toArray($request);
        $res['is_admin'] = (bool) $user->is_admin;
        $res['is_owner'] = true;
        $res['likes_count'] = AccountService::getAccountLikesCount($pid);

        return response()->json(['data' => $res]);
    }

    public function getAccountInfo(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        $profile = Profile::find($id);

        if (! $profile || $request->user()->cannot('view', $profile)) {
            return $this->error('Account not found or is unavailable', 404);
        }

        $res = (new ProfileResource($profile))->toArray($request);
        $res['is_owner'] = false;
        $res['likes_count'] = AccountService::getAccountLikesCount($id);

        return response()->json(['data' => $res]);
    }

    public function notifications(Request $request)
    {
        $pid = $request->user()->profile_id;

        return NotificationResource::collection(
            Notification::whereUserId($pid)
                ->whereIn('type', [11, 15, 16, 21, 22, 23])
                ->orderByDesc('id')
                ->cursorPaginate(20)
        );
    }

    public function notificationUnreadCount(Request $request)
    {
        $pid = $request->user()->profile_id;

        return $this->data(['unread_count' => NotificationService::getUnreadCount($pid)]);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $pid = $request->user()->profile_id;

        $notify = Notification::whereUserId($pid)->findOrFail($id);
        $notify->read_at = now();
        $notify->save();

        NotificationService::clearUnreadCount($pid);

        return new NotificationResource($notify);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $pid = $request->user()->profile_id;
        Notification::whereUserId($pid)->update(['read_at' => now()]);
        NotificationService::clearUnreadCount($pid);

        return $this->success();
    }

    public function accountBlock(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        abort_if($pid == $id, 403, 'You cannot block yourself');

        $profile = Profile::findOrFail($id);

        UserFilter::updateOrCreate([
            'profile_id' => $pid,
            'account_id' => $profile->id,
        ]);

        Follower::whereProfileId($pid)->whereFollowingId($profile->id)->delete();
        Follower::whereProfileId($profile->id)->whereFollowingId($pid)->delete();

        FollowerService::refreshAndSync($pid, $profile->id);
        $res = (new ProfileResource($profile))->toArray($request);
        $res['is_blocking'] = true;

        return response()->json($res);
    }

    public function accountUnblock(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        abort_if($pid == $id, 403, 'You cannot unblock yourself');

        $profile = Profile::findOrFail($id);

        UserFilter::whereProfileId($pid)
            ->whereAccountId($profile->id)
            ->delete();

        FollowerService::refreshAndSync($pid, $profile->id);
        $res = (new ProfileResource($profile))->toArray($request);
        $res['is_blocking'] = false;

        return response()->json($res);
    }

    public function follow(Request $request, $id)
    {
        $pid = $request->user()->profile_id;

        if ($pid == $id) {
            return $this->error('You cannot follow your own account', 422);
        }

        $profile = Profile::findOrFail($id);

        if ($request->user()->cannot('view', $profile)) {
            return $this->error('Account not found or is unavailable', 404);
        }

        if ($profile->local) {
            $res = Follower::updateOrCreate([
                'profile_id' => $pid,
                'following_id' => $id,
            ], [
                'following_is_local' => (bool) $profile->local,
            ]);

            if ($res->wasRecentlyCreated) {
                NotificationService::newFollower($id, $pid);
            }
        } else {
            if (FollowRequest::whereProfileId($pid)->whereFollowingId($profile->id)->exists()) {
                return $this->data(AccountService::get($id));
            }

            $res = FollowRequest::firstOrCreate([
                'profile_id' => $pid,
                'following_id' => $profile->id,
                'profile_is_local' => true,
                'following_is_local' => false,
                'following_state' => 0,
            ]);

            DeliverFollowRequest::dispatch($res)->onQueue('activitypub-out');
        }

        return $this->data(AccountService::get($id));
    }

    public function unfollow(Request $request, $id)
    {
        $pid = $request->user()->profile_id;

        if ($pid == $id) {
            return $this->error('You cannot unfollow your own account', 422);
        }

        $profile = Profile::findOrFail($id);

        if ($profile->local) {
            $res = Follower::where([
                'profile_id' => $pid,
                'following_id' => $id,
            ])->first();

            if (! $res) {
                return $this->data(AccountService::get($id));
            }
            $res->delete();
            NotificationService::unFollow($id, $pid);
        } else {
            $followRequest = FollowRequest::whereProfileId($pid)->whereFollowingId($profile->id)->first();
            if ($followRequest) {
                DeliverUndoFollowActivity::dispatch($followRequest)->onQueue('activitypub-out');
            }
        }

        return $this->data(AccountService::get($id));
    }

    public function undoFollowRequest(Request $request, $id)
    {
        $pid = $request->user()->profile_id;

        if ($pid == $id) {
            return $this->error('You cannot unfollow your own account', 422);
        }

        $profile = Profile::findOrFail($id);
        $followRequest = FollowRequest::whereProfileId($pid)->whereFollowingId($profile->id)->firstOrFail();

        if ($profile->local) {
            $followRequest->delete();

            return $this->data(AccountService::get($id));
        } else {
            $followRequest->update(['following_state' => 5]);
            DeliverUndoFollowActivity::dispatch($followRequest)->onQueue('activitypub-out');
        }

        return $this->data(AccountService::get($id));
    }

    public function getRelationshipState(Request $request, $id)
    {
        $pid = $request->user()->profile_id;
        if ($id == $pid) {
            return $this->data([
                'following' => false,
                'blocking' => false,
            ]);
        }

        $account = AccountService::get($id);
        if (! $account) {
            $this->error('Record not found');
        }
        $res = [
            'pending_follow_request' => false,
            'following' => (bool) FollowerService::follows($pid, $id),
            'blocking' => (bool) UserFilterService::isBlocking($pid, $id),
        ];

        if (! $account['local']) {
            $res['pending_follow_request'] = FollowRequest::whereProfileId($pid)->whereFollowingId($id)->whereFollowingState(0)->exists();
        }

        return $this->data($res);
    }

    public function getSuggestedAccounts(Request $request)
    {
        $pid = $request->user()->profile_id;

        $accounts = Profile::whereStatus(1)
            ->whereLocal(true)
            ->where('id', '!=', $pid)
            ->orderByDesc('followers')
            ->take(10)
            ->pluck('id');

        $res = $accounts->map(function ($id) {
            return AccountService::get($id);
        })->filter(function ($acct) use ($pid) {
            if (! $acct) {
                return false;
            }

            if (FollowerService::follows($pid, $acct['id'])) {
                return false;
            }

            return true;
        })->shuffle()->toArray();

        return $this->data($res);
    }

    public function accountFollowers(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        if ($request->user() && $request->user()->cannot('viewAny', [Profile::class])) {
            return $this->error('Unavailable', 403);
        }

        $followers = Follower::whereFollowingId($id)->orderByDesc('id')->cursorPaginate(15);

        return FollowerResource::collection($followers);
    }

    public function accountFollowing(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        if ($request->user() && $request->user()->cannot('viewAny', [Profile::class])) {
            return $this->error('Unavailable', 403);
        }

        $followers = Follower::whereProfileId($id)->orderByDesc('id')->cursorPaginate(15);

        return FollowingResource::collection($followers);
    }
}
