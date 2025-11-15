<?php

namespace App\Models;

use App\Jobs\Federation\DiscoverInstance;
use App\Services\ActivityService;
use App\Services\SanitizeService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $domain
 * @property string $uri
 * @property string|null $key_id
 * @property string|null $public_key
 * @property string|null $shared_inbox
 * @property int|null $instance_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $last_fetched_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereLastFetchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereSharedInbox($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstanceActor whereUri($value)
 *
 * @mixin \Eloquent
 */
class InstanceActor extends Model
{
    public $guarded = [];

    public $casts = [
        'last_fetched_at' => 'datetime',
    ];

    /**
     * Find or create a remote instance actor from a URL
     */
    public static function findOrCreateFromUrl(string $url): ?self
    {
        $validUrl = app(SanitizeService::class)->url($url, true);

        if (! $validUrl) {
            return null;
        }

        if ($res = self::where('uri', $url)->first()) {
            return $res;
        }

        $actorData = app(ActivityService::class)->fetchRemoteActor($url);

        if (! $actorData || ! isset($actorData['id'], $actorData['preferredUsername'], $actorData['inbox'], $actorData['type'])) {
            return null;
        }

        $domain = parse_url($url, PHP_URL_HOST);

        if (! $actorData['type'] || $actorData['type'] !== 'Application' || $actorData['id'] != $url) {
            return null;
        }

        $actor = static::firstOrNew([
            'uri' => $actorData['id'],
        ]);

        $instanceId = Instance::where('domain', $domain)->first();
        $keyId = data_get($actorData, 'publicKey.id');
        $publicKey = data_get($actorData, 'publicKey.publicKeyPem');
        $sharedInbox = data_get($actorData, 'endpoints.sharedInbox');
        $actor->forceFill([
            'domain' => $domain,
            'key_id' => $keyId,
            'public_key' => $publicKey,
            'shared_inbox' => $sharedInbox,
            'instance_id' => $instanceId ? $instanceId->id : null,
            'last_fetched_at' => now(),
        ])->save();

        if (! $instanceId) {
            DiscoverInstance::dispatch($url);
        }

        return $actor;
    }
}
