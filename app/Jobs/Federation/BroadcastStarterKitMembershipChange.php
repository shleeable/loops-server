<?php

namespace App\Jobs\Federation;

use App\Federation\ActivityBuilders\StarterKitMembershipActivityBuilder;
use App\Models\Profile;
use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BroadcastStarterKitMembershipChange implements ShouldQueue
{
    use Batchable, Queueable;

    public StarterKit $kit;

    public StarterKitAccount $account;

    public string $changeType;

    public $tries = 2;

    public $timeout = 120;

    public function __construct(StarterKit $kit, StarterKitAccount $account, string $changeType)
    {
        if (! in_array($changeType, ['Add', 'Remove'], true)) {
            throw new \InvalidArgumentException("Invalid changeType: {$changeType}");
        }

        $this->kit = $kit;
        $this->account = $account;
        $this->changeType = $changeType;
    }

    public function handle(): void
    {
        $kit = $this->kit->fresh(['profile']);
        $account = StarterKitAccount::withTrashed()->with('profile')->find($this->account->id);

        if (! $kit || ! $account) {
            $this->delete();

            return;
        }

        if (! $kit->is_local || ! $kit->can_federate) {
            $this->delete();

            return;
        }

        $owner = $kit->profile;
        $member = $account->profile;

        if (! $owner instanceof Profile || ! $member instanceof Profile || ! $owner->local) {
            $this->delete();

            return;
        }

        $activity = $this->changeType === 'Add'
            ? StarterKitMembershipActivityBuilder::buildAdd($owner, $kit, $account, $member)
            : StarterKitMembershipActivityBuilder::buildRemove($owner, $kit, $account, $member);

        $inboxes = $this->resolveInboxes($kit, $owner, $member);

        if ($inboxes->isEmpty()) {
            Log::info('No remote inboxes for starter kit membership change', [
                'kit_id' => $kit->id,
                'account_id' => $account->id,
                'type' => $this->changeType,
            ]);

            return;
        }

        foreach ($inboxes as $inboxUrl) {
            DeliverStarterKitActivity::dispatch(
                $owner,
                $inboxUrl,
                $activity,
                "starter-kit-{$this->changeType}-{$account->id}-".md5($inboxUrl),
            )->onQueue('activitypub-out');
        }
    }

    private function resolveInboxes(StarterKit $kit, Profile $owner, Profile $member): \Illuminate\Support\Collection
    {
        $followerInboxes = DB::table('followers')
            ->where('followers.following_id', $owner->id)
            ->join('profiles', 'profiles.id', '=', 'followers.profile_id')
            ->where('profiles.local', false)
            ->select('profiles.shared_inbox_url', 'profiles.inbox_url')
            ->get();

        $memberInboxes = DB::table('starter_kit_accounts')
            ->where('starter_kit_accounts.starter_kit_id', $kit->id)
            ->whereNull('starter_kit_accounts.deleted_at')
            ->where('starter_kit_accounts.kit_status', StarterKitAccount::STATUS_APPROVED)
            ->join('profiles', 'profiles.id', '=', 'starter_kit_accounts.profile_id')
            ->where('profiles.local', false)
            ->select('profiles.shared_inbox_url', 'profiles.inbox_url')
            ->get();

        $affected = collect();
        if (! $member->local) {
            $affected->push((object) [
                'shared_inbox_url' => $member->shared_inbox_url,
                'inbox_url' => $member->inbox_url,
            ]);
        }

        return $followerInboxes
            ->concat($memberInboxes)
            ->concat($affected)
            ->map(fn ($row) => $row->shared_inbox_url ?: $row->inbox_url)
            ->filter()
            ->unique()
            ->values();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BroadcastStarterKitMembershipChange failed', [
            'kit_id' => $this->kit->id,
            'account_id' => $this->account->id,
            'type' => $this->changeType,
            'error' => $exception->getMessage(),
        ]);
    }
}
