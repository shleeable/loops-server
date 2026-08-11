<?php

namespace App\Services;

use App\Events\DmMessageCreated;
use App\Events\DmMessageDeleted;
use App\Jobs\Federation\DeliverDmActivity;
use App\Models\Conversation;
use App\Models\ConversationContext;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Video;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @phpstan-type DmMediaAttributes array{
 *     profile_id: int,
 *     type: string,
 *     mime_type: string|null,
 *     remote_url: string,
 *     preview_remote_url?: string|null,
 *     width?: int|null,
 *     height?: int|null,
 *     blurhash?: string|null,
 *     description?: string|null,
 *     provider?: string,
 *     external_id?: string
 * }
 */
class DirectMessageService
{
    public const INBOUND_REQUEST_SOFT_CAP = 5;

    protected const CONTEXT_KEYS = [
        'context_uri',
        'remote_conversation_uri',
        'remote_context_uri',
        'context',
        'conversation',
        'ostatus:conversation',
    ];

    protected const MAX_CONTEXT_LENGTH = 255;

    public function __construct(
        protected DmActivityPubService $ap
    ) {}

    public function canMessage(Profile $sender, Profile $recipient): bool
    {
        return $this->recipientState($sender, $recipient) !== false;
    }

    /**
     * Resolve the participant state a recipient gets for messages from the
     * given sender, following the per-recipient-silently model: undeliverable
     * recipients are excluded rather than failing the whole send.
     *
     * Remote recipients always resolve to ACTIVE; their own server enforces
     * their privacy settings.
     *
     * @return string|false ConversationParticipant::STATE_* or false when undeliverable
     */
    protected function recipientState(Profile $sender, Profile $recipient): string|false
    {
        if ($sender->id === $recipient->id) {
            return false;
        }

        if ($this->isBlocked($sender, $recipient)) {
            return false;
        }

        if ($this->isRemote($recipient)) {
            return ConversationParticipant::STATE_ACTIVE;
        }

        return match ($this->dmPrivacySetting($recipient)) {
            'off' => false,
            'following' => $this->follows($recipient, $sender)
                ? ConversationParticipant::STATE_ACTIVE
                : ConversationParticipant::STATE_REQUEST,
            'everyone' => ConversationParticipant::STATE_ACTIVE,
            default => false,
        };
    }

    public function isRestrictedSender(Profile $sender): bool
    {
        $minAccountAgeDays = (int) config('loops.dm.compose.min_account_age_days');
        $minFollowers = (int) config('loops.dm.compose.min_followers');

        return $sender->created_at->gt(now()->subDays($minAccountAgeDays))
            || (int) $sender->followers < $minFollowers;
    }

    public function canInitiateConversation(Profile $sender, Profile $recipient): bool
    {
        if (! $this->isRestrictedSender($sender)) {
            return true;
        }

        $conversationExists = Conversation::where(
            'participants_hash',
            Conversation::dmHash($sender->id, $recipient->id)
        )->exists();

        if ($conversationExists) {
            return true;
        }

        return $this->isMutual($sender, $recipient);
    }

    public function send(Profile $sender, Profile $recipient, array $payload): Message
    {
        $conversation = $this->getOrCreateConversation($sender, $recipient);

        return $this->sendMessage($sender, $conversation, $payload);
    }

    public function getOrCreateConversation(Profile $sender, Profile $recipient): Conversation
    {
        abort_unless(
            $this->canInitiateConversation($sender, $recipient),
            403,
            'You do not have permission for this action'
        );

        $state = $this->recipientState($sender, $recipient);

        abort_if($state === false, 403, 'You cannot message this account.');

        return DB::transaction(function () use ($sender, $recipient, $state) {
            $conversation = $this->findOrCreateDm($sender, $recipient);

            $senderParticipant = $this->ensureParticipant(
                $conversation,
                $sender->id,
                ConversationParticipant::STATE_ACTIVE
            );
            $senderParticipant->forceFill([
                'state' => ConversationParticipant::STATE_ACTIVE,
                'hidden_at' => null,
            ])->save();

            $this->ensureParticipant($conversation, $recipient->id, $state);

            return $conversation->load('participants.profile');
        });
    }

    /**
     * context_uri is always minted by us. Remote thread identifiers are
     * recorded separately on remote_context_uri / remote_conversation_uri
     * and indexed in conversation_contexts.
     */
    public function findOrCreateDm(Profile $a, Profile $b): Conversation
    {
        $conversation = Conversation::firstOrCreate(
            ['participants_hash' => Conversation::dmHash($a->id, $b->id)],
            [
                'type' => Conversation::TYPE_DM,
                'created_by_profile_id' => $a->id,
                'context_uri' => url('/ap/contexts/'.Str::uuid()),
            ]
        );

        $this->registerOwnContext($conversation);

        return $conversation;
    }

    /**
     * Create a group conversation. Recipients who cannot receive messages
     * from the creator (blocked, DMs off) are silently excluded.
     *
     * @param  iterable<Profile>  $recipients
     */
    public function createGroup(Profile $creator, iterable $recipients): Conversation
    {
        /** @var Collection<int, Profile> $recipients */
        $recipients = collect($recipients)
            ->unique('id')
            ->reject(fn (Profile $profile) => $profile->id === $creator->id)
            ->values();

        abort_if(
            $recipients->count() < 2,
            422,
            'Group conversations need at least two other participants.'
        );

        $max = $this->maxGroupParticipants();

        abort_if(
            $recipients->count() + 1 > $max,
            422,
            "Group conversations are limited to {$max} participants."
        );

        if ($this->isRestrictedSender($creator)) {
            abort_unless(
                $recipients->every(fn (Profile $profile) => $this->isMutual($creator, $profile)),
                403,
                'You do not have permission for this action'
            );
        }

        $states = $recipients->mapWithKeys(
            fn (Profile $profile) => [$profile->id => $this->recipientState($creator, $profile)]
        );

        $deliverable = $recipients->filter(
            fn (Profile $profile) => $states[$profile->id] !== false
        );

        abort_if($deliverable->isEmpty(), 403, 'Message not deliverable.');

        return DB::transaction(function () use ($creator, $deliverable, $states) {
            $seedIds = $deliverable->pluck('id')->push($creator->id)->all();

            $conversation = Conversation::create([
                'type' => Conversation::TYPE_GROUP,
                'created_by_profile_id' => $creator->id,
                'context_uri' => url('/ap/contexts/'.Str::uuid()),
                'seed_participants_hash' => Conversation::groupHash($seedIds),
            ]);

            $this->registerOwnContext($conversation);

            $this->ensureParticipant(
                $conversation,
                $creator->id,
                ConversationParticipant::STATE_ACTIVE
            );

            foreach ($deliverable as $profile) {
                $this->ensureParticipant($conversation, $profile->id, $states[$profile->id]);
            }

            return $conversation->load('participants.profile');
        });
    }

    /**
     * Add participants to an existing group. Undeliverable recipients are
     * silently skipped. Members who previously left are re-added.
     *
     * @param  iterable<Profile>  $recipients
     */
    public function addParticipants(Profile $actor, Conversation $conversation, iterable $recipients): Conversation
    {
        abort_unless($conversation->isGroup(), 422, 'Not a group conversation.');

        $actorParticipant = $conversation->participantFor($actor->id);

        abort_unless(
            $actorParticipant && $actorParticipant->state === ConversationParticipant::STATE_ACTIVE,
            403,
            'You do not have permission for this action'
        );

        /** @var Collection<int, Profile> $recipients */
        $recipients = collect($recipients)
            ->unique('id')
            ->reject(fn (Profile $profile) => $profile->id === $actor->id)
            ->values();

        $max = $this->maxGroupParticipants();
        $current = $conversation->participants()
            ->where('state', '!=', ConversationParticipant::STATE_LEFT)
            ->count();

        abort_if(
            $current + $recipients->count() > $max,
            422,
            "Group conversations are limited to {$max} participants."
        );

        DB::transaction(function () use ($actor, $conversation, $recipients) {
            foreach ($recipients as $profile) {
                $state = $this->recipientState($actor, $profile);

                if ($state === false) {
                    continue;
                }

                $participant = $this->ensureParticipant($conversation, $profile->id, $state);

                if ($participant->state === ConversationParticipant::STATE_LEFT) {
                    $participant->forceFill([
                        'state' => $state,
                        'hidden_at' => null,
                    ])->save();
                }
            }
        });

        return $conversation->load('participants.profile');
    }

    public function leaveGroup(Profile $profile, Conversation $conversation): void
    {
        abort_unless($conversation->isGroup(), 422, 'Not a group conversation.');

        $participant = $conversation->participantFor($profile->id);

        abort_unless($participant !== null, 404, 'Conversation not found.');

        $participant->forceFill([
            'state' => ConversationParticipant::STATE_LEFT,
            'hidden_at' => now(),
        ])->save();
    }

    /**
     * Send a message into an existing conversation (1:1 or group).
     */
    public function sendMessage(Profile $sender, Conversation $conversation, array $payload): Message
    {
        $conversation->loadMissing('participants.profile');

        $senderParticipant = $conversation->participantFor($sender->id);

        abort_unless(
            $senderParticipant && $senderParticipant->state !== ConversationParticipant::STATE_LEFT,
            403,
            'You are not part of this conversation.'
        );

        if ($conversation->type === Conversation::TYPE_DM) {
            $recipientParticipant = $conversation->otherParticipant($sender->id);
            $recipient = $recipientParticipant?->profile;

            abort_unless(
                $recipient && $this->canMessage($sender, $recipient),
                403,
                'You cannot message this account.'
            );

            if (
                $recipientParticipant->state === ConversationParticipant::STATE_REQUEST
                && $conversation->messages()->where('profile_id', $sender->id)->exists()
            ) {
                abort(403, 'Message request pending. You can send more messages once they accept.');
            }
        }

        $message = DB::transaction(function () use ($sender, $senderParticipant, $conversation, $payload) {
            $senderParticipant->forceFill([
                'state' => ConversationParticipant::STATE_ACTIVE,
                'hidden_at' => null,
            ])->save();

            $replyParentId = $payload['in_reply_to_id'] ?? $conversation->last_message_id;

            $message = $conversation->messages()->create([
                'profile_id' => $sender->id,
                'type' => $payload['type'],
                'body' => $payload['body'] ?? null,
                'video_id' => $payload['video_id'] ?? null,
                'in_reply_to_id' => $replyParentId,
            ]);

            if (isset($payload['video_id'])) {
                Video::where('id', $payload['video_id'])->increment('shares');
            }

            $message->forceFill([
                'ap_object_uri' => $this->ap->objectUri($message),
            ])->save();

            /** @var list<DmMediaAttributes> $mediaList */
            $mediaList = $payload['media'] ?? [];
            $this->attachMedia($message, $mediaList);

            $this->touchConversation($conversation, $message);

            $senderParticipant->forceFill([
                'last_read_message_id' => $message->id,
            ])->save();

            $this->unhideActiveParticipants($conversation, $sender->id);

            return $message->load(['sender', 'video', 'media', 'conversation.participants.profile']);
        });

        // event(new DmMessageCreated($message));

        $this->deliverToRemoteParticipants($message);
        $this->notifyLocalParticipants($message);

        return $message;
    }

    /**
     * Handle an inbound Create activity.
     *
     *
     * @param  Collection<int, Profile>  $recipients
     */
    public function receive(Profile $sender, Collection $recipients, array $payload): ?Message
    {
        $recipients = $recipients
            ->unique('id')
            ->reject(fn (Profile $profile) => $profile->id === $sender->id)
            ->values();

        if ($recipients->isEmpty()) {
            return null;
        }

        $objectUri = $payload['object_uri'] ?? null;

        if (! is_string($objectUri) || $objectUri === '') {
            return null;
        }

        if (Message::withTrashed()->where('ap_object_uri', $objectUri)->exists()) {
            return null;
        }

        $states = $recipients->mapWithKeys(
            fn (Profile $profile) => [$profile->id => $this->recipientState($sender, $profile)]
        );

        $hasDeliverableLocal = $recipients->contains(
            fn (Profile $profile) => ! $this->isRemote($profile) && $states[$profile->id] !== false
        );

        if (! $hasDeliverableLocal) {
            return null;
        }

        $message = DB::transaction(function () use ($sender, $recipients, $states, $payload, $objectUri) {
            $conversation = $this->resolveInboundConversation($sender, $recipients, $payload);

            $this->rememberRemoteThreadUris($conversation, $payload);

            $senderParticipant = $this->ensureParticipant(
                $conversation,
                $sender->id,
                ConversationParticipant::STATE_ACTIVE
            );
            $senderParticipant->forceFill([
                'state' => ConversationParticipant::STATE_ACTIVE,
                'hidden_at' => null,
            ])->save();

            $recipientParticipants = [];

            foreach ($recipients as $profile) {
                $state = $states[$profile->id];

                if ($state === false) {
                    continue;
                }

                $recipientParticipants[$profile->id] = $this->ensureParticipant(
                    $conversation,
                    $profile->id,
                    $state
                );
            }

            if (! $conversation->isGroup()) {
                $recipientParticipant = $recipientParticipants[$recipients->first()->id] ?? null;

                if (! $recipientParticipant) {
                    return;
                }

                if (
                    $recipientParticipant->state === ConversationParticipant::STATE_REQUEST
                    && $conversation->messages()->where('profile_id', $sender->id)->count()
                        >= self::INBOUND_REQUEST_SOFT_CAP
                ) {
                    return;
                }
            }

            $message = $conversation->messages()->create([
                'profile_id' => $sender->id,
                'type' => ! empty($payload['media']) ? Message::TYPE_MEDIA : Message::TYPE_TEXT,
                'body' => $payload['body'] ?? null,
                'in_reply_to_id' => $this->threadParentId($conversation, $payload),
            ]);

            $message->forceFill([
                'ap_object_uri' => $objectUri,
            ])->save();

            /** @var list<DmMediaAttributes> $mediaList */
            $mediaList = $payload['media'] ?? [];
            $this->attachMedia($message, $mediaList);

            $this->touchConversation($conversation, $message);

            $this->unhideActiveParticipants($conversation, $sender->id);

            return $message->load(['sender', 'video', 'media', 'conversation.participants.profile']);
        });

        if (! $message) {
            return null;
        }

        // event(new DmMessageCreated($message));

        $this->notifyLocalParticipants($message);

        return $message;
    }

    /**
     * Resolve which conversation an inbound message belongs to.
     *
     * @param  Collection<int, Profile>  $recipients
     */
    protected function resolveInboundConversation(
        Profile $sender,
        Collection $recipients,
        array $payload
    ): Conversation {
        $contextUris = $this->contextUris($payload);

        foreach ($contextUris as $contextUri) {
            $candidate = $this->conversationByContext($contextUri);

            if ($candidate && $this->canJoinExisting($candidate, $sender, $recipients)) {
                $this->linkContexts($candidate, $contextUris);

                return $candidate;
            }
        }

        $parent = $this->inboundParent($payload);

        if ($parent?->conversation && $this->canJoinExisting($parent->conversation, $sender, $recipients)) {
            $this->linkContexts($parent->conversation, $contextUris);

            return $parent->conversation;
        }

        $isGroup = $recipients->count() > 1;

        $candidate = $isGroup
            ? $this->findGroupByParticipants($sender, $recipients)
            : Conversation::with('participants')
                ->where('participants_hash', Conversation::dmHash($sender->id, $recipients->first()->id))
                ->first();

        if (! $candidate) {
            $candidate = $isGroup
                ? $this->createInboundGroup($sender, $recipients)
                : $this->findOrCreateDm($sender, $recipients->first());
        }

        $this->linkContexts($candidate, $contextUris);

        return $candidate;
    }

    /**
     * Every usable thread identifier in the payload, deduped and in
     * precedence order.
     *
     * @return list<string>
     */
    protected function contextUris(array $payload): array
    {
        $uris = [];

        foreach (self::CONTEXT_KEYS as $key) {
            $uri = $this->normalizeContextValue($payload[$key] ?? null);

            if ($uri !== null) {
                $uris[$uri] = true;
            }
        }

        return array_keys($uris);
    }

    protected function normalizeContextValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value['id'] ?? null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || strlen($value) > self::MAX_CONTEXT_LENGTH) {
            return null;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL) && ! Str::startsWith($value, 'tag:')) {
            return null;
        }

        return $value;
    }

    /**
     * Record the remote's own thread identifiers so buildNote() can echo them
     * back as `conversation`
     *
     * Never stores a URI in our own namespace.
     */
    protected function rememberRemoteThreadUris(Conversation $conversation, array $payload): void
    {
        $localHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        $updates = [];

        $candidates = [
            'remote_context_uri' => $payload['remote_context_uri'] ?? $payload['context'] ?? null,
            'remote_conversation_uri' => $payload['remote_conversation_uri'] ?? $payload['conversation'] ?? null,
        ];

        foreach ($candidates as $column => $value) {
            if ($conversation->{$column}) {
                continue;
            }

            $uri = $this->normalizeContextValue($value);

            if (! $uri) {
                continue;
            }

            $host = strtolower((string) parse_url($uri, PHP_URL_HOST));

            if ($host !== '' && $host === $localHost) {
                continue;
            }

            $updates[$column] = $uri;
        }

        if ($updates) {
            $conversation->forceFill($updates)->save();
        }
    }

    protected function conversationByContext(string $contextUri): ?Conversation
    {
        $conversationId = ConversationContext::where('context_uri', $contextUri)
            ->value('conversation_id');

        if ($conversationId) {
            return Conversation::with('participants')->find($conversationId);
        }

        return Conversation::with('participants')
            ->where('context_uri', $contextUri)
            ->first();
    }

    /**
     * Record remote thread identifiers as aliases for one of our convos
     *
     * @param  list<string>  $contextUris
     */
    protected function linkContexts(Conversation $conversation, array $contextUris): void
    {
        foreach ($contextUris as $contextUri) {
            if ($contextUri === $conversation->context_uri) {
                continue;
            }

            ConversationContext::firstOrCreate(
                ['context_uri' => $contextUri],
                ['conversation_id' => $conversation->id]
            );
        }
    }

    protected function registerOwnContext(Conversation $conversation): void
    {
        if (! $conversation->context_uri) {
            return;
        }

        ConversationContext::firstOrCreate(
            ['context_uri' => $conversation->context_uri],
            ['conversation_id' => $conversation->id]
        );
    }

    protected function inboundParent(array $payload): ?Message
    {
        $parentId = $payload['in_reply_to_id'] ?? null;

        if (is_numeric($parentId)) {
            return Message::withTrashed()
                ->with('conversation.participants')
                ->find((int) $parentId);
        }

        $parentUri = $payload['in_reply_to'] ?? null;

        if (! is_string($parentUri) || $parentUri === '') {
            return null;
        }

        return Message::withTrashed()
            ->with('conversation.participants')
            ->where('ap_object_uri', $parentUri)
            ->first();
    }

    protected function threadParentId(Conversation $conversation, array $payload): ?int
    {
        $parent = $this->inboundParent($payload);

        if (! $parent || (int) $parent->conversation_id !== (int) $conversation->id) {
            return null;
        }

        return (int) $parent->id;
    }

    /**
     * Whether an inbound sender may post into an existing conversation.
     *
     * @param  Collection<int, Profile>  $recipients
     */
    protected function canJoinExisting(
        Conversation $conversation,
        Profile $sender,
        Collection $recipients
    ): bool {
        $conversation->loadMissing('participants');

        if (! $conversation->participants->firstWhere('profile_id', $sender->id)) {
            return false;
        }

        if ($conversation->isGroup()) {
            return true;
        }

        if ($recipients->count() !== 1) {
            return false;
        }

        $existing = $conversation->participants->pluck('profile_id')
            ->map(fn ($id) => (int) $id)->sort()->values()->all();

        $envelope = collect([$sender->id, $recipients->first()->id])
            ->map(fn ($id) => (int) $id)->sort()->values()->all();

        return $existing === $envelope;
    }

    /**
     * Last-resort group match on the seed participant set.
     *
     * @param  Collection<int, Profile>  $recipients
     */
    protected function findGroupByParticipants(Profile $sender, Collection $recipients): ?Conversation
    {
        $ids = $recipients->pluck('id')->push($sender->id)->all();

        $candidates = Conversation::with('participants')
            ->where('type', Conversation::TYPE_GROUP)
            ->where('seed_participants_hash', Conversation::groupHash($ids))
            ->limit(2)
            ->get();

        return $candidates->count() === 1 ? $candidates->first() : null;
    }

    /**
     * @param  Collection<int, Profile>  $recipients
     */
    protected function createInboundGroup(Profile $sender, Collection $recipients): Conversation
    {
        $seedIds = $recipients->pluck('id')->push($sender->id)->all();

        $conversation = Conversation::create([
            'type' => Conversation::TYPE_GROUP,
            'created_by_profile_id' => $sender->id,
            'context_uri' => url('/ap/contexts/'.Str::uuid()),
            'seed_participants_hash' => Conversation::groupHash($seedIds),
        ]);

        $this->registerOwnContext($conversation);

        return $conversation;
    }

    public function deleteMessage(Message $message): void
    {
        $conversation = $message->conversation;
        $conversation->loadMissing('participants.profile');
        $wasLast = (int) $conversation->last_message_id === (int) $message->id;

        $authorIsLocal = $message->sender && ! $this->isRemote($message->sender);
        $activity = $authorIsLocal && $message->ap_object_uri ? $this->ap->buildDelete($message) : null;
        $authorId = (int) $message->profile_id;
        $remotes = $this->remoteParticipantProfiles($conversation, $authorId);

        $message->delete();

        if ($wasLast) {
            $latest = $conversation->messages()->orderByDesc('id')->first();
            $conversation->forceFill([
                'last_message_id' => $latest?->id,
                'last_message_at' => $latest?->created_at,
            ])->save();
        }

        // event(new DmMessageDeleted($conversation, (string) $message->id));

        if ($activity && $remotes->isNotEmpty()) {
            foreach ($this->ap->inboxUrls($remotes) as $inbox) {
                DeliverDmActivity::dispatch($activity, $inbox, $authorId);
            }
        }
    }

    protected function deliverToRemoteParticipants(Message $message): void
    {
        $remotes = $this->remoteParticipantProfiles($message->conversation, (int) $message->profile_id)
            ->reject(fn (Profile $profile) => $this->isBlocked($message->sender, $profile));

        if ($remotes->isEmpty()) {
            return;
        }

        $activity = $this->ap->buildCreate($message);

        foreach ($this->ap->inboxUrls($remotes) as $inbox) {
            DeliverDmActivity::dispatch($activity, $inbox, (int) $message->profile_id);
        }
    }

    protected function notifyLocalParticipants(Message $message): void
    {
        $message->conversation->participants
            ->where('profile_id', '!=', $message->profile_id)
            ->filter(fn (ConversationParticipant $participant) => $participant->state === ConversationParticipant::STATE_ACTIVE
                && ! $participant->isMuted()
                && $participant->profile
                && ! $this->isRemote($participant->profile)
                && ! $this->isBlocked($participant->profile, $message->sender))
            ->each(fn (ConversationParticipant $participant) => $this->sendPushNotification($participant->profile, $message));
    }

    /**
     * All remote, non-left participants except the given profile.
     *
     * @return Collection<int, Profile>
     */
    protected function remoteParticipantProfiles(Conversation $conversation, int $exceptProfileId): Collection
    {
        return $conversation->participants
            ->where('profile_id', '!=', $exceptProfileId)
            ->where('state', '!=', ConversationParticipant::STATE_LEFT)
            ->map(fn (ConversationParticipant $participant) => $participant->profile)
            ->filter(fn (?Profile $profile) => $profile && $this->isRemote($profile))
            ->values();
    }

    /**
     * @param  list<DmMediaAttributes>  $mediaList
     */
    protected function attachMedia(Message $message, array $mediaList): void
    {
        if (! $mediaList) {
            return;
        }

        $order = 0;
        $entities = [];

        foreach ($mediaList as $attributes) {
            $media = $message->media()->create(array_merge($attributes, ['order' => $order++]));
            $entities[] = $media->toEntity();
        }

        $message->forceFill(['entities' => ['media' => $entities]])->save();
    }

    protected function ensureParticipant(Conversation $conversation, int $profileId, string $initialState): ConversationParticipant
    {
        return ConversationParticipant::firstOrCreate(
            [
                'conversation_id' => $conversation->id,
                'profile_id' => $profileId,
            ],
            [
                'state' => $initialState,
            ]
        );
    }

    protected function touchConversation(Conversation $conversation, Message $message): void
    {
        $conversation->forceFill([
            'last_message_id' => $message->id,
            'last_message_at' => $message->created_at,
        ])->save();
    }

    protected function unhideActiveParticipants(Conversation $conversation, int $exceptProfileId): void
    {
        $conversation->participants()
            ->where('profile_id', '!=', $exceptProfileId)
            ->where('state', ConversationParticipant::STATE_ACTIVE)
            ->whereNotNull('hidden_at')
            ->update(['hidden_at' => null]);
    }

    protected function maxGroupParticipants(): int
    {
        return (int) config('loops.dm.groups.max_participants', 12);
    }

    protected function isRemote(Profile $profile): bool
    {
        return $profile->local == false;
    }

    protected function isBlocked(Profile $a, Profile $b): bool
    {
        $filters = app(UserFilterService::class);

        return $filters->isBlocking($a->id, $b->id);
    }

    protected function follows(Profile $profile, Profile $target): bool
    {
        return FollowerService::follows($profile->id, $target->id);
    }

    protected function isMutual(Profile $a, Profile $b): bool
    {
        return $this->follows($a, $b) && $this->follows($b, $a);
    }

    protected function dmPrivacySetting(Profile $profile): string
    {
        return $profile->dm_privacy ?? 'everyone';
    }

    protected function sendPushNotification(Profile $recipient, Message $message): void
    {
        // todo
    }
}
