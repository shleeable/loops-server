<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Observers\ProfileObserver;
use App\Services\ActivityService;
use App\Services\AutoLinkerService;
use App\Services\SanitizeService;
use App\Services\SigningService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stevebauman\Purify\Facades\Purify;

#[ObservedBy([ProfileObserver::class])]
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string $username
 * @property string|null $bio
 * @property string|null $avatar
 * @property int $status
 * @property int $followers
 * @property int $following
 * @property int $video_count
 * @property bool $local
 * @property string|null $uri
 * @property string|null $domain
 * @property string|null $public_key
 * @property string|null $inbox_url
 * @property string|null $outbox_url
 * @property string|null $shared_inbox_url
 * @property string|null $following_url
 * @property string|null $followers_url
 * @property int $is_suspended
 * @property int $is_hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $links
 * @property string|null $last_fetched_at
 * @property string|null $last_fetch_failure_at
 * @property string|null $last_delivery_failure_at
 * @property int $fetch_failure_count
 * @property int $delivery_failure_count
 * @property string|null $admin_notes
 * @property int $can_upload
 * @property int $can_follow
 * @property int $can_comment
 * @property int $can_like
 * @property int $can_share
 * @property int $manuallyApprovesFollowers
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Video> $videos
 * @property-read int|null $videos_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCanComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCanFollow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCanLike($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCanShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCanUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereDeliveryFailureCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFetchFailureCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFollowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFollowersUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFollowing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFollowingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereInboxUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereIsSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLastDeliveryFailureAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLastFetchFailureAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLastFetchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLinks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereOutboxUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereSharedInboxUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereVideoCount($value)
 *
 * @mixin \Eloquent
 */
class Profile extends Model
{
    use HasFactory, HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'username',
        'name',
        'bio',
        'avatar',
        'followers',
        'following',
        'can_upload',
        'can_follow',
        'can_comment',
        'can_like',
        'can_share',
        'manuallyApprovesFollowers',
        'domain',
        'uri',
        'inbox_url',
        'outbox_url',
        'followers_url',
        'following_url',
        'shared_inbox_url',
        'public_key',
        'last_fetched_at',
        'local',
    ];

    protected $guarded = [];

    protected $casts = [
        'links' => 'array',
        'local' => 'boolean',
        'manuallyApprovesFollowers' => 'boolean',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function deepLink()
    {
        $q = http_build_query([
            'avatar' => $this->avatar,
            'username' => $this->username,
            'name' => $this->name,
            'src' => 'api',
        ]);

        return 'pfloops://profile/id/'.$this->id.'?'.$q;
    }

    public function getPublicUrl()
    {
        return url('/@'.$this->username);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActorId($suffix = null)
    {
        return $this->local ?
            url('/ap/users/'.$this->id.$suffix) :
            $this->uri;
    }

    public function getFollowingUrl()
    {
        return $this->local ?
            $this->getActorId('/following') :
            $this->following_url;
    }

    public function getFollowersUrl()
    {
        return $this->local ?
            $this->getActorId('/followers') :
            $this->followers_url;
    }

    public function getOutboxUrl()
    {
        return $this->local ?
            $this->getActorId('/outbox') :
            $this->outbox_url;
    }

    public function profileAvatar()
    {
        return $this->hasOne(ProfileAvatar::class);
    }

    /**
     * Get the actor's public key ID for HTTP signatures
     */
    public function getKeyId(): string
    {
        return $this->getActorId().'#main-key';
    }

    /**
     * Convert to ActivityPub JSON representation
     */
    public function toActivityPub(): array
    {
        if (! $this->local) {
            return [];
        }

        $res = [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $this->getActorId(),
            'type' => 'Person',
            'preferredUsername' => $this->username,
            'name' => $this->name,
            'summary' => AutoLinkerService::link($this->bio),
            'inbox' => $this->getActorId('/inbox'),
            'outbox' => $this->getActorId('/outbox'),
            'followers' => $this->getActorId('/followers'),
            'following' => $this->getActorId('/following'),
            'manuallyApprovesFollowers' => $this->manuallyApprovesFollowers,
            'url' => url('/@'.$this->username),
            'publicKey' => [
                'id' => $this->getKeyId(),
                'owner' => $this->getActorId(),
                'publicKeyPem' => app(SigningService::class)->getPublicKey(),
            ],
        ];

        if ($this->avatar) {
            $res['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => $this->avatar,
            ];
        } else {
            $res['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => url('/storage/avatars/default.jpg'),
            ];
        }

        return $res;
    }

    /**
     * Find or create a remote actor from an ActivityPub URL
     */
    public static function findOrCreateFromUrl(string $url, ?array $actorData = null, $forceRefresh = false): ?self
    {
        $validUrl = app(SanitizeService::class)->url($url, true);

        if (! $validUrl) {
            return null;
        }

        $actor = static::firstOrNew([
            'uri' => $url,
            'local' => false,
        ]);

        if ($actor->wasRecentlyCreated || $forceRefresh) {
            $actorData = app(ActivityService::class)->fetchRemoteActor($url);

            if (! $actorData || ! isset($actorData['id'], $actorData['preferredUsername'], $actorData['inbox'], $actorData['type'])) {
                return null;
            }

            if (! $actorData['type'] || $actorData['type'] !== 'Person') {
                return null;
            }

            $domain = parse_url($url, PHP_URL_HOST);
            $username = $actorData['preferredUsername'];
            $acct = $username.'@'.$domain;
            $sharedInbox = data_get($actorData, 'endpoints.sharedInbox');
            $avatar = data_get($actorData, 'icon.url');
            $actor->forceFill([
                'username' => $acct,
                'name' => Purify::clean($actorData['name'] ?? $username),
                'bio' => app(SanitizeService::class)->cleanHtmlWithSpacing($actorData['summary'] ?? null),
                'inbox_url' => $actorData['inbox'],
                'avatar' => $avatar,
                'uri' => $actorData['id'],
                'outbox_url' => $actorData['outbox'] ?? null,
                'followers_url' => $actorData['followers'] ?? null,
                'following_url' => $actorData['following'] ?? null,
                'public_key' => $actorData['publicKey']['publicKeyPem'] ?? null,
                'manuallyApprovesFollowers' => data_get($actorData, 'manuallyApprovesFollowers', false),
                'last_fetched_at' => now(),
                'local' => false,
                'domain' => $domain,
                'shared_inbox_url' => $sharedInbox,
            ])->save();
        }

        return $actor;
    }
}
