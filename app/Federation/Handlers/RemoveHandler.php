<?php

namespace App\Federation\Handlers;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Services\StarterKitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemoveHandler extends BaseHandler
{
    public function handle(array $activity, ?Profile $actor = null, ?Profile $target = null): void
    {
        $objectUrl = is_array($activity['object']) ? ($activity['object']['id'] ?? null) : ($activity['object'] ?? null);
        $targetUrl = is_array($activity['target']) ? ($activity['target']['id'] ?? null) : ($activity['target'] ?? null);

        if (! is_string($objectUrl) || ! is_string($targetUrl)) {
            return;
        }

        if (! $actor) {
            if (config('logging.dev_log')) {
                Log::warning('Rejecting Remove — no resolved actor', ['object' => $objectUrl]);
            }

            return;
        }

        $actorHost = parse_url($actor->getActorId(), PHP_URL_HOST);
        $targetHost = parse_url($targetUrl, PHP_URL_HOST);

        if (! $actorHost || ! $targetHost || $actorHost !== $targetHost) {
            if (config('logging.dev_log')) {
                Log::warning('Rejecting Remove — target host does not match actor host', [
                    'actor' => $actor->id,
                    'actor_host' => $actorHost,
                    'target_host' => $targetHost,
                ]);
            }

            return;
        }

        $this->removeFeaturedCollection($actor, $objectUrl);
    }

    private function removeFeaturedCollection(Profile $actor, string $objectUrl): void
    {
        $kit = StarterKit::where('is_local', false)
            ->where(function ($q) use ($objectUrl) {
                $q->where('remote_url', $objectUrl)
                    ->orWhere('remote_object_url', $objectUrl);
            })
            ->first();

        if (! $kit) {
            if (config('logging.dev_log')) {
                Log::info('Remove FeaturedCollection received for unknown kit', [
                    'object' => $objectUrl,
                ]);
            }

            return;
        }

        if ((string) $kit->profile_id !== (string) $actor->id) {
            if (config('logging.dev_log')) {
                Log::warning('Rejecting Remove FeaturedCollection by non-owner', [
                    'actor' => $actor->id,
                    'kit_owner' => $kit->profile_id,
                    'kit_id' => $kit->id,
                ]);
            }

            return;
        }

        $kitId = $kit->id;

        DB::transaction(function () use ($kit) {
            Notification::whereIn('type', Notification::starterKitTypes())->whereNotNull('meta')->where('meta->starter_kit_id', $kit->id)->delete();
            StarterKitAccount::where('starter_kit_id', $kit->id)->forceDelete();
            $kit->starterKitTags()->delete();
            $kit->delete();
        });

        app(StarterKitService::class)->forget($kitId);

        if (config('logging.dev_log')) {
            Log::info('Successfully handled Remove FeaturedCollection', [
                'kit_id' => $kitId,
                'object' => $objectUrl,
                'actor' => $actor->id,
            ]);
        }
    }
}
