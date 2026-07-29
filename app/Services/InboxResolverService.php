<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InboxResolverService
{
    /**
     * Get grouped inbox URLs for a profile's remote followers.
     * Groups by shared_inbox_url when available to minimize deliveries.
     *
     * @param  int  $profileId  The profile whose followers to deliver to
     * @return Collection Collection of ['inbox' => string, 'profile_ids' => array]
     */
    public function getFollowerInboxes(int $profileId): Collection
    {
        return DB::table('followers')
            ->where('followers.following_id', $profileId)
            ->where('followers.profile_is_local', false)
            ->join('profiles', 'profiles.id', '=', 'followers.profile_id')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->whereNotNull('profiles.inbox_url')
            ->where('instances.is_blocked', false)
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->get()
            ->groupBy('inbox')
            ->map(fn ($group) => [
                'inbox' => $group->first()->inbox,
                'profile_ids' => $group->pluck('profile_id')->toArray(),
            ])
            ->values();
    }

    /**
     * Get inbox URLs for remote profiles who reposted an object.
     * Must be called before the object row is deleted, since repost
     * rows cascade with it.
     *
     * @param  string  $type  video|comment|comment_reply
     * @return Collection Collection of ['inbox' => string, 'profile_ids' => array]
     */
    public function getShareInboxes(string $type, $id): Collection
    {
        $tables = [
            'video' => ['video_reposts', 'video_id'],
            'comment' => ['comment_reposts', 'comment_id'],
            'comment_reply' => ['comment_reply_reposts', 'reply_id'],
        ];

        if (! isset($tables[$type])) {
            return collect();
        }

        [$table, $column] = $tables[$type];

        return DB::table($table)
            ->join('profiles', 'profiles.id', '=', $table.'.profile_id')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->where($table.'.'.$column, $id)
            ->where('profiles.local', false)
            ->whereNotNull('profiles.inbox_url')
            ->where(function ($q) {
                $q->whereNull('instances.is_blocked')->orWhere('instances.is_blocked', false);
            })
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->get()
            ->groupBy('inbox')
            ->map(fn ($group) => [
                'inbox' => $group->first()->inbox,
                'profile_ids' => $group->pluck('profile_id')->unique()->values()->toArray(),
            ])
            ->values();
    }

    /**
     * Get shared inboxes for the largest known instances by user count.
     *
     * Best-effort coverage for public content that reached instances outside the
     * follower graph via relays. Public objects only.
     *
     * @return Collection Collection of ['inbox' => string, 'profile_ids' => array]
     */
    public function getTopInstanceInboxes(int $limit = 50): Collection
    {
        return Cache::remember("federation:top-instance-inboxes:{$limit}", 604800, function () use ($limit) {
            return DB::table('instances')
                ->join('profiles', 'profiles.domain', '=', 'instances.domain')
                ->where('instances.is_blocked', false)
                ->where('profiles.local', false)
                ->whereNotNull('profiles.shared_inbox_url')
                ->whereNotNull('instances.user_count')
                ->groupBy('instances.domain')
                ->selectRaw('instances.domain as domain, MIN(profiles.shared_inbox_url) as inbox, MAX(instances.user_count) as total_users')
                ->orderByDesc('total_users')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'inbox' => $row->inbox,
                    'profile_ids' => [],
                ])
                ->values();
        });
    }

    /**
     * Get inbox URLs for mentioned profiles (remote only).
     *
     * Accepts Mention models, Profile models, or raw profile ids.
     *
     * @return Collection Collection of ['inbox' => string, 'profile_ids' => array]
     */
    public function getMentionInboxes($mentions): Collection
    {
        $mentions = collect($mentions);

        if ($mentions->isEmpty()) {
            return collect();
        }

        $profileIds = $mentions->map(function ($mention) {
            if ($mention instanceof \App\Models\Profile) {
                return $mention->id;
            }

            if (is_object($mention)) {
                return $mention->profile_id ?? null;
            }

            return $mention;
        })->filter()->unique()->values();

        if ($profileIds->isEmpty()) {
            return collect();
        }

        return DB::table('profiles')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->whereIn('profiles.id', $profileIds)
            ->where('profiles.local', false)
            ->whereNotNull('profiles.inbox_url')
            ->where(function ($q) {
                $q->whereNull('instances.is_blocked')->orWhere('instances.is_blocked', false);
            })
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->get()
            ->groupBy('inbox')
            ->map(fn ($group) => [
                'inbox' => $group->first()->inbox,
                'profile_ids' => $group->pluck('profile_id')->unique()->values()->toArray(),
            ])
            ->values();
    }

    /**
     * Get ALL known remote inboxes for broadcast deletions.
     * Used when you need to send Delete activities to everyone who might have a copy.
     * Groups by shared_inbox_url when available to minimize deliveries.
     *
     * @return Collection Collection of ['inbox' => string, 'profile_ids' => array]
     */
    public function getAllKnownInboxes(): Collection
    {
        return DB::table('profiles')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->where('profiles.local', false)
            ->whereNotNull('profiles.inbox_url')
            ->where('instances.is_blocked', false)
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->get()
            ->groupBy('inbox')
            ->map(function ($group) {
                return [
                    'inbox' => $group->first()->inbox,
                    'profile_ids' => $group->pluck('profile_id')->toArray(),
                ];
            })
            ->values();
    }

    /**
     * Get follower inboxes with chunking for large follower counts.
     *
     * @param  callable  $callback  Called with each chunk of inboxes
     */
    public function chunkFollowerInboxes(int $profileId, callable $callback, int $chunkSize = 500): void
    {
        $processedInboxes = collect();

        DB::table('followers')
            ->where('followers.following_id', $profileId)
            ->where('followers.profile_is_local', false)
            ->join('profiles', 'profiles.id', '=', 'followers.profile_id')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->whereNotNull('profiles.inbox_url')
            ->where('instances.is_blocked', false)
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->chunkById($chunkSize, function ($followers) use (&$processedInboxes) {
                $grouped = $followers->groupBy('inbox');

                $inboxGroup = [];
                foreach ($grouped as $inboxUrl => $group) {
                    $inboxGroup[$inboxUrl] = [
                        'inbox' => $group->first()->inbox,
                        'profile_ids' => $group->pluck('profile_id')->toArray(),
                    ];
                }

                foreach ($inboxGroup as $inboxUrl => $data) {
                    if ($processedInboxes->has($inboxUrl)) {
                        $existing = $processedInboxes->get($inboxUrl);
                        $existing['profile_ids'] = array_merge(
                            $existing['profile_ids'],
                            $data['profile_ids']
                        );
                        $processedInboxes->put($inboxUrl, $existing);
                    } else {
                        $processedInboxes->put($inboxUrl, $data);
                    }
                }
            }, 'profiles.id', 'profile_id');

        if ($processedInboxes->isNotEmpty()) {
            $callback($processedInboxes->values());
        }
    }

    /**
     * Get all known remote inboxes with chunking, returns flat inbox URLs.
     * Processes in chunks to avoid memory issues on large instances.
     *
     * @param  callable  $callback  Called with Collection of inbox URL strings
     */
    public function chunkAllKnownInboxesFlat(callable $callback, int $chunkSize = 500): void
    {
        DB::table('profiles')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->where('profiles.local', false)
            ->whereNotNull('profiles.inbox_url')
            ->where('instances.is_blocked', false)
            ->selectRaw('DISTINCT COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox')
            ->orderBy('inbox')
            ->chunk($chunkSize, function ($rows) use ($callback) {
                $inboxes = $rows->pluck('inbox');

                if ($inboxes->isNotEmpty()) {
                    $callback($inboxes);
                }
            });
    }

    /**
     * Get all known remote inboxes with chunking for large instance counts.
     *
     * @param  callable  $callback  Called with each chunk of inboxes
     */
    public function chunkAllKnownInboxes(callable $callback, int $chunkSize = 500): void
    {
        $processedInboxes = collect();

        DB::table('profiles')
            ->leftJoin('instances', 'instances.domain', '=', 'profiles.domain')
            ->where('profiles.local', false)
            ->whereNotNull('profiles.inbox_url')
            ->where('instances.is_blocked', false)
            ->select([
                'profiles.id as profile_id',
                DB::raw('COALESCE(profiles.shared_inbox_url, profiles.inbox_url) as inbox'),
            ])
            ->chunkById($chunkSize, function ($profiles) use (&$processedInboxes) {
                $grouped = $profiles->groupBy('inbox');

                $inboxGroup = [];
                foreach ($grouped as $inboxUrl => $group) {
                    $inboxGroup[$inboxUrl] = [
                        'inbox' => $group->first()->inbox,
                        'profile_ids' => $group->pluck('profile_id')->toArray(),
                    ];
                }

                foreach ($inboxGroup as $inboxUrl => $data) {
                    if ($processedInboxes->has($inboxUrl)) {
                        $existing = $processedInboxes->get($inboxUrl);
                        $existing['profile_ids'] = array_merge(
                            $existing['profile_ids'],
                            $data['profile_ids']
                        );
                        $processedInboxes->put($inboxUrl, $existing);
                    } else {
                        $processedInboxes->put($inboxUrl, $data);
                    }
                }
            }, 'profiles.id', 'profile_id');

        if ($processedInboxes->isNotEmpty()) {
            $callback($processedInboxes->values());
        }
    }
}
