<?php

namespace App\Federation\Handlers;

use App\Models\Follower;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\UserFilter;
use App\Models\UserInterest;
use App\Services\NotificationService;
use App\Services\SanitizeService;
use App\Services\UserFilterService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlockHandler extends BaseHandler
{
    public function handle(array $activity, Profile $actor, ?Profile $target = null)
    {
        $objectUrl = $activity['object'];

        try {
            DB::beginTransaction();

            $targetProfile = $this->findLocalProfile($objectUrl);

            if (! $targetProfile) {
                if (config('logging.dev_log')) {
                    Log::info('Block target is not a local profile, ignoring', [
                        'actor' => $actor->username,
                        'object' => $objectUrl,
                    ]);
                }

                DB::commit();

                return;
            }

            if ((int) $actor->id === (int) $targetProfile->id) {
                if (config('logging.dev_log')) {
                    Log::info('Ignoring self-block activity', [
                        'actor' => $actor->username,
                        'object' => $objectUrl,
                    ]);
                }

                DB::commit();

                return;
            }

            $existingBlock = UserFilter::where('profile_id', $actor->id)
                ->where('account_id', $targetProfile->id)
                ->first();

            if ($existingBlock) {
                if (config('logging.dev_log')) {
                    Log::info('Block already exists', [
                        'actor' => $actor->username,
                        'target' => $targetProfile->username,
                    ]);
                }

                DB::commit();

                return $existingBlock;
            }

            $filter = UserFilter::create([
                'profile_id' => $actor->id,
                'account_id' => $targetProfile->id,
            ]);

            $this->removeFollowRelationships($actor, $targetProfile);
            $this->removeNotifications($actor, $targetProfile);
            $this->removeUserInterests($actor, $targetProfile);

            DB::commit();

            UserFilterService::getAll($actor->id, true);
            UserFilterService::getAll($targetProfile->id, true);

            if (config('logging.dev_log')) {
                Log::info('Successfully handled Block activity', [
                    'actor' => $actor->username,
                    'target' => $targetProfile->username,
                    'activity_id' => $activity['id'] ?? 'unknown',
                ]);
            }

            return $filter;

        } catch (\Exception $e) {
            DB::rollBack();

            if (config('logging.dev_log')) {
                Log::error('Failed to handle Block activity', [
                    'actor' => $actor->username,
                    'object' => $objectUrl,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            throw $e;
        }
    }

    private function findLocalProfile(string $url): ?Profile
    {
        if (! $this->isLocalObject($url)) {
            return null;
        }

        $match = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{userId}',
                '/@{username}',
            ],
            useAppHost: true,
            constraints: [
                'userId' => '\d+',
                'username' => '[a-zA-Z0-9_](?:(?:[a-zA-Z0-9_]|[.-](?![.-]))*[a-zA-Z0-9_])?',
            ]
        );

        if (! $match) {
            return null;
        }

        if (isset($match['userId']) && $match['userId']) {
            return Profile::whereKey($match['userId'])
                ->where('local', true)
                ->whereNull('domain')
                ->first();
        }

        if (isset($match['username']) && $match['username']) {
            return Profile::whereUsername($match['username'])
                ->where('local', true)
                ->whereNull('domain')
                ->first();
        }

        return null;
    }

    private function removeFollowRelationships(Profile $actor, Profile $target): void
    {
        Follower::where('profile_id', $actor->id)
            ->where('following_id', $target->id)
            ->delete();

        Follower::where('profile_id', $target->id)
            ->where('following_id', $actor->id)
            ->delete();
    }

    private function removeNotifications(Profile $actor, Profile $target): void
    {
        Notification::where('user_id', $target->id)->where('profile_id', $actor->id)->delete();
        NotificationService::clearUnreadCount($target->id);
    }

    private function removeUserInterests(Profile $actor, Profile $target): void
    {
        UserInterest::where('profile_id', $target->id)->where('interest_type', 'creator')->where('interest_value', $actor->id)->delete();
    }
}
