<?php

namespace App\Jobs\StarterKit;

use App\Models\Profile;
use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Services\ActivityPubService;
use App\Services\SanitizeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStarterKitItemsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public array $backoff = [60, 300, 900];

    public int $uniqueFor = 600;

    public function __construct(public int $starterKitId) {}

    public function uniqueId(): string
    {
        return (string) $this->starterKitId;
    }

    public function handle(ActivityPubService $activityPub, SanitizeService $sanitize): void
    {
        $kit = StarterKit::find($this->starterKitId);

        if (! $kit || $kit->is_local) {
            return;
        }

        if (! $kit->remote_object_url) {
            Log::warning('SyncStarterKitItems: kit missing remote_object_url', ['kit_id' => $kit->id]);

            return;
        }

        $kitData = $this->fetchActivity($activityPub, $kit->remote_object_url);

        if (! $kitData || ($kitData['type'] ?? null) !== 'FeaturedCollection') {
            Log::warning('SyncStarterKitItems: failed to fetch kit collection', [
                'kit_id' => $kit->id,
                'url' => $kit->remote_object_url,
            ]);

            return;
        }

        $items = data_get($kitData, 'orderedItems', []);

        if (! is_array($items) || empty($items)) {
            $this->pruneStale($kit, []);
            $kit->syncAccountCount();

            return;
        }

        $seenItemIds = [];
        $order = 0;

        foreach ($items as $item) {
            $order++;

            if (! is_array($item)) {
                continue;
            }

            if (($item['type'] ?? null) !== 'FeaturedItem') {
                continue;
            }

            // Temporarily disable this due to Mastodon bug https://github.com/mastodon/mastodon/issues/39544
            // if (($item['featuredObjectType'] ?? null) !== 'Person') {
            //     continue;
            // }

            $itemId = $item['id'] ?? null;
            $featuredObject = $item['featuredObject'] ?? null;
            $attestationUrl = $item['featureAuthorization'] ?? null;

            if (! is_string($itemId) || ! is_string($featuredObject) || ! is_string($attestationUrl)) {
                continue;
            }

            if (! $sanitize->url($itemId, true)
                || ! $sanitize->url($featuredObject, true)
                || ! $sanitize->url($attestationUrl, true)) {
                continue;
            }

            try {
                $account = $this->syncItem(
                    kit: $kit,
                    itemId: $itemId,
                    featuredObject: $featuredObject,
                    attestationUrl: $attestationUrl,
                    order: $order,
                    activityPub: $activityPub,
                    sanitize: $sanitize,
                );

                if ($account) {
                    $seenItemIds[] = $itemId;
                }
            } catch (\Throwable $e) {
                Log::warning('SyncStarterKitItems: item failed', [
                    'kit_id' => $kit->id,
                    'item' => $itemId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->pruneStale($kit, $seenItemIds);
        $kit->syncAccountCount();
    }

    protected function syncItem(
        StarterKit $kit,
        string $itemId,
        string $featuredObject,
        string $attestationUrl,
        int $order,
        ActivityPubService $activityPub,
        SanitizeService $sanitize,
    ): ?StarterKitAccount {
        $attestation = $this->fetchActivity($activityPub, $attestationUrl);

        if (! $attestation) {
            Log::info('SyncStarterKitItems: attestation fetch failed', [
                'kit_id' => $kit->id,
                'url' => $attestationUrl,
            ]);

            return null;
        }

        if (($attestation['type'] ?? null) !== 'FeatureAuthorization') {
            Log::info('SyncStarterKitItems: attestation type mismatch', [
                'kit_id' => $kit->id,
                'got' => $attestation['type'] ?? null,
            ]);

            return null;
        }

        if (($attestation['id'] ?? null) !== $attestationUrl) {
            Log::info('SyncStarterKitItems: attestation id mismatch', [
                'expected' => $attestationUrl,
                'got' => $attestation['id'] ?? null,
            ]);

            return null;
        }

        if (($attestation['interactingObject'] ?? null) !== $kit->remote_object_url) {
            Log::info('SyncStarterKitItems: attestation interactingObject mismatch', [
                'kit_id' => $kit->id,
                'expected' => $kit->remote_object_url,
                'got' => $attestation['interactingObject'] ?? null,
            ]);

            return null;
        }

        if (($attestation['interactionTarget'] ?? null) !== $featuredObject) {
            Log::info('SyncStarterKitItems: attestation interactionTarget mismatch', [
                'expected' => $featuredObject,
                'got' => $attestation['interactionTarget'] ?? null,
            ]);

            return null;
        }

        if (parse_url($attestationUrl, PHP_URL_HOST) !== parse_url($kit->remote_object_url, PHP_URL_HOST)) {
            Log::info('SyncStarterKitItems: attestation cross-origin', [
                'kit_host' => parse_url($kit->remote_object_url, PHP_URL_HOST),
                'attestation_host' => parse_url($attestationUrl, PHP_URL_HOST),
            ]);

            return null;
        }

        $profile = app(Profile::class)->findOrCreateFromUrl($featuredObject);

        if (! $profile) {
            Log::info('SyncStarterKitItems: could not resolve featured profile', [
                'url' => $featuredObject,
            ]);

            return null;
        }

        $account = StarterKitAccount::withTrashed()
            ->where('starter_kit_id', $kit->id)
            ->where('profile_id', $profile->id)
            ->first();

        if ($account && $account->trashed()) {
            $account->restore();
        }

        if (! $account) {
            $account = new StarterKitAccount;
        }

        $account->fill([
            'starter_kit_id' => $kit->id,
            'profile_id' => $profile->id,
            'remote_object_id' => $itemId,
            'attestation_url' => $attestationUrl,
            'kit_account_local' => false,
            'kit_status' => StarterKitAccount::STATUS_APPROVED,
            'order' => $order,
            'approved_at' => $account->approved_at ?? now(),
            'rejected_at' => null,
        ])->save();

        return $account;
    }

    protected function pruneStale(StarterKit $kit, array $seenItemIds): void
    {
        StarterKitAccount::where('starter_kit_id', $kit->id)
            ->where('kit_account_local', false)
            ->when(
                ! empty($seenItemIds),
                fn ($q) => $q->whereNotIn('remote_object_id', $seenItemIds),
            )
            ->delete();
    }

    protected function fetchActivity(ActivityPubService $activityPub, string $url): ?array
    {
        $response = $activityPub->get($url);

        if (! $response) {
            return null;
        }

        if (is_array($response)) {
            return $response;
        }

        if (method_exists($response, 'json')) {
            $json = $response->json();

            return is_array($json) ? $json : null;
        }

        return null;
    }
}
