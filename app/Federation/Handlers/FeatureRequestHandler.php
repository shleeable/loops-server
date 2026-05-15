<?php

namespace App\Federation\Handlers;

use App\Jobs\Federation\DeliverFeatureRequestAccept;
use App\Jobs\Federation\DeliverFeatureRequestReject;
use App\Models\Profile;
use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Services\FollowerService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class FeatureRequestHandler extends BaseHandler
{
    public function handle(array $activity, Profile $actor, Profile $target): void
    {
        $kit = app(StarterKit::class)->findOrCreateFromUrl($activity['instrument']);

        if (! $kit || $kit->is_local) {
            Log::warning('FeatureRequest: kit unresolvable or not remote', [
                'instrument' => $activity['instrument'],
            ]);

            return;
        }

        if ((int) $kit->profile_id !== (int) $actor->id) {
            Log::warning('FeatureRequest: actor does not own kit', [
                'kit_id' => $kit->id,
                'kit_owner' => $kit->profile_id,
                'actor' => $actor->id,
            ]);

            return;
        }

        $existing = StarterKitAccount::withTrashed()
            ->where('starter_kit_id', $kit->id)
            ->where('profile_id', $target->id)
            ->first();

        if ($existing) {
            $this->handleDuplicate($existing, $target, $activity['id']);

            return;
        }

        $account = StarterKitAccount::create([
            'starter_kit_id' => $kit->id,
            'profile_id' => $target->id,
            'remote_object_id' => $activity['id'],
            'kit_account_local' => $target->local,
            'kit_status' => StarterKitAccount::STATUS_PENDING,
            'order' => 0,
        ]);

        $this->dispatchByPreference($account, $actor, $target, $activity['id']);
    }

    protected function handleDuplicate(StarterKitAccount $account, Profile $target, string $activityId): void
    {
        if ($account->trashed()) {
            return;
        }

        match ($account->kit_status) {
            StarterKitAccount::STATUS_APPROVED => DeliverFeatureRequestAccept::dispatch($target, $account, $activityId),

            StarterKitAccount::STATUS_REJECTED => DeliverFeatureRequestReject::dispatch($target, $account, $activityId),

            default => null,
        };
    }

    protected function dispatchByPreference(
        StarterKitAccount $account,
        Profile $actor,
        Profile $target,
        string $activityId,
    ): void {
        $state = (int) ($target->starter_kit_state ?? 0);

        if ($state === 0) {
            $this->handleRejection($account, $target, $activityId);

            return;
        }

        $doesFollow = (bool) FollowerService::follows($target->id, $actor->id);

        if ($state === 1) {
            if ($doesFollow) {
                $this->handleApproval($account, $target, $activityId);
                $this->sendStarterKitAutoAddYouFollowNotification($target, $actor, $account->starter_kit_id);

                return;
            } else {
                $this->handleRejection($account, $target, $activityId);

                return;
            }
        }

        if ($state === 2) {
            if ($doesFollow) {
                $this->sendApprovalRequestNotification($target, $actor, $account->starter_kit_id);

                return;
            } else {
                $this->handleRejection($account, $target, $activityId);

                return;
            }
        }

        if ($state === 3) {
            // ignore, not yet implemented
            return;
        }

        if ($state === 4) {
            // ignore, not yet implemented
            return;
        }

        if ($state === 5) {
            $this->sendApprovalRequestNotification($target, $actor, $account->starter_kit_id);

            return;
        }

        if ($state === 6) {
            $this->handleApproval($account, $target, $activityId);
            $this->sendStarterKitAutoAddNotification($target, $actor, $account->starter_kit_id);

            return;
        }

    }

    protected function sendApprovalRequestNotification(Profile $target, Profile $actor, int $kitId)
    {
        NotificationService::starterKitAddAccount($target->id, $actor->id, $kitId);
    }

    protected function sendStarterKitAutoAddNotification(Profile $target, Profile $actor, int $kitId)
    {
        NotificationService::starterKitAutoAdd($target->id, $actor->id, $kitId);
    }

    protected function sendStarterKitAutoAddYouFollowNotification(Profile $target, Profile $actor, int $kitId)
    {
        NotificationService::starterKitAutoAddYouFollow($target->id, $actor->id, $kitId);
    }

    protected function handleApproval(
        StarterKitAccount $account,
        Profile $target,
        string $activityId
    ) {
        $account->update([
            'kit_status' => StarterKitAccount::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        DeliverFeatureRequestAccept::dispatch($target, $account, $activityId);
    }

    protected function handleRejection(
        StarterKitAccount $account,
        Profile $target,
        string $activityId
    ) {
        $account->update([
            'kit_status' => StarterKitAccount::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        DeliverFeatureRequestReject::dispatch($target, $account, $activityId);
    }
}
