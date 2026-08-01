<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShareSheetService
{
    const CONVERSATIONS_KEY = 'loops:servss:dm:sheet:convos:';

    const FOLLOWING_KEY = 'loops:servss:dm:sheet:follows:';

    const CONVERSATIONS_TTL = 21600;

    const FOLLOWING_TTL = 21600;

    const FOLLOWING_FRESH = 600;

    const POOL_LIMIT = 24;

    public static function suggested(Profile $profile, bool $includeGroups = false, int $limit = 12): array
    {
        $suggestions = [];
        $seenProfiles = [];

        foreach (self::conversationPool($profile) as $entry) {
            if (count($suggestions) >= $limit) {
                break;
            }

            if ($entry['kind'] === 'group' && ! $includeGroups) {
                continue;
            }

            if ($entry['kind'] === 'account') {
                $seenProfiles[$entry['id']] = true;
            }

            $suggestions[] = $entry;
        }

        if (count($suggestions) >= $limit) {
            return $suggestions;
        }

        foreach (self::followingPool($profile) as $entry) {
            if (count($suggestions) >= $limit) {
                break;
            }

            if (isset($seenProfiles[$entry['id']])) {
                continue;
            }

            $seenProfiles[$entry['id']] = true;
            $suggestions[] = $entry;
        }

        return $suggestions;
    }

    public static function conversationPool(Profile $profile): array
    {
        return Cache::remember(
            self::CONVERSATIONS_KEY.$profile->id,
            self::CONVERSATIONS_TTL,
            fn () => self::buildConversationPool($profile)
        );
    }

    public static function followingPool(Profile $profile): array
    {
        return Cache::flexible(
            self::FOLLOWING_KEY.$profile->id,
            [self::FOLLOWING_FRESH, self::FOLLOWING_TTL],
            fn () => self::buildFollowingPool($profile)
        );
    }

    public static function forgetConversations(int $profileId): void
    {
        Cache::forget(self::CONVERSATIONS_KEY.$profileId);
    }

    public static function forgetFollowing(int $profileId): void
    {
        Cache::forget(self::FOLLOWING_KEY.$profileId);
    }

    public static function forget(int $profileId): void
    {
        self::forgetConversations($profileId);
        self::forgetFollowing($profileId);
    }

    public static function forgetForConversation(Conversation $conversation): void
    {
        $conversation->participants()
            ->pluck('profile_id')
            ->each(fn ($profileId) => self::forgetConversations((int) $profileId));
    }

    public static function forgetForFollow(int $actorProfileId, int $targetProfileId): void
    {
        self::forgetFollowing($actorProfileId);
        self::forgetFollowing($targetProfileId);
    }

    protected static function buildConversationPool(Profile $profile): array
    {
        $profileId = $profile->id;

        $participants = ConversationParticipant::query()
            ->select('conversation_participants.*', 'conversations.last_message_at as sort_last_message_at')
            ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
            ->where('conversation_participants.profile_id', $profileId)
            ->where('conversation_participants.state', ConversationParticipant::STATE_ACTIVE)
            ->whereNull('conversation_participants.hidden_at')
            ->whereNotNull('conversations.last_message_at')
            ->orderByRaw('conversation_participants.muted_at is null desc')
            ->orderByDesc('sort_last_message_at')
            ->limit(self::POOL_LIMIT * 2)
            ->with('conversation.participants.profile')
            ->get();

        $seenProfiles = [];
        $pool = [];

        foreach ($participants as $participant) {
            if (count($pool) >= self::POOL_LIMIT) {
                break;
            }

            $conversation = $participant->conversation;

            if ($conversation === null) {
                continue;
            }

            if ($conversation->type === Conversation::TYPE_GROUP) {
                $others = $conversation->participants
                    ->where('profile_id', '!=', $profileId)
                    ->filter(fn (ConversationParticipant $other) => $other->state !== ConversationParticipant::STATE_LEFT
                        && $other->profile !== null)
                    ->values();

                if ($others->isEmpty()) {
                    continue;
                }

                $names = $others
                    ->map(fn (ConversationParticipant $other) => $other->profile->name
                        ?? explode('@', $other->profile->username)[0])
                    ->filter()
                    ->values();

                $display = $conversation->title
                    ?: ($names->count() <= 3
                        ? $names->implode(', ')
                        : $names->take(3)->implode(', ').' +'.($names->count() - 3));

                $pool[] = [
                    'kind' => 'group',
                    'id' => (string) $conversation->id,
                    'conversation_id' => (string) $conversation->id,
                    'username' => null,
                    'name' => $display,
                    'avatar' => null,
                    'avatars' => $others
                        ->take(2)
                        ->map(fn (ConversationParticipant $other) => $other->profile->avatar)
                        ->filter()
                        ->values()
                        ->all(),
                    'member_count' => $others->count() + 1,
                    'domain' => null,
                    'is_remote' => false,
                ];

                continue;
            }

            $other = $conversation->otherParticipant($profileId)?->profile;

            if (! $other || isset($seenProfiles[$other->id])) {
                continue;
            }

            $seenProfiles[$other->id] = true;
            $pool[] = self::accountEntry($other);
        }

        return $pool;
    }

    protected static function buildFollowingPool(Profile $profile): array
    {
        $profileId = $profile->id;

        $candidates = Profile::query()
            ->select('profiles.*', 'followers.created_at as followed_at')
            ->selectRaw(
                'exists(select 1 from followers as mutual where mutual.profile_id = followers.following_id and mutual.following_id = ?) as is_mutual',
                [$profileId]
            )
            ->join('followers', 'followers.following_id', '=', 'profiles.id')
            ->where('followers.profile_id', $profileId)
            ->where('profiles.id', '!=', $profileId)
            ->where('profiles.status', '=', 1)
            ->where(function ($q) use ($profileId) {
                $q->where('profiles.dm_privacy', 'everyone')
                    ->orWhere(function ($q) use ($profileId) {
                        $q->where('profiles.dm_privacy', 'following')
                            ->whereExists(function ($sub) use ($profileId) {
                                $sub->select(DB::raw(1))
                                    ->from('followers as mutual')
                                    ->whereColumn('mutual.profile_id', 'followers.following_id')
                                    ->where('mutual.following_id', $profileId);
                            });
                    });
            })
            ->orderByDesc('is_mutual')
            ->orderByDesc('followers.created_at')
            ->limit(self::POOL_LIMIT * 3)
            ->get();

        $dms = app(DirectMessageService::class);
        $pool = [];

        foreach ($candidates as $candidate) {
            if (count($pool) >= self::POOL_LIMIT) {
                break;
            }

            if (! $dms->canMessage($profile, $candidate)) {
                continue;
            }

            if (! $dms->canInitiateConversation($profile, $candidate)) {
                continue;
            }

            $pool[] = self::accountEntry($candidate);
        }

        return $pool;
    }

    protected static function accountEntry(Profile $profile): array
    {
        return [
            'kind' => 'account',
            'id' => (string) $profile->id,
            'username' => $profile->username,
            'name' => $profile->name ?? $profile->username,
            'avatar' => $profile->avatar ?? null,
            'domain' => $profile->domain,
            'is_remote' => $profile->domain !== null,
        ];
    }
}
