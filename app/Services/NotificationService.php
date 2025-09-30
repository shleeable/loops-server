<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    const NOTIFY_UNREAD_KEY = 'api:s:notify:unread:';

    public static function getUnreadCount($profileId)
    {
        if (! $profileId) {
            return 0;
        }

        return Cache::remember(
            self::NOTIFY_UNREAD_KEY.$profileId,
            now()->addHours(24),
            function () use ($profileId) {
                return Notification::whereUserId($profileId)->whereNull('read_at')->count();
            }
        );
    }

    public static function clearUnreadCount($profileId)
    {
        if (! $profileId) {
            return 0;
        }

        return Cache::forget(self::NOTIFY_UNREAD_KEY.$profileId);
    }

    public static function newVideoLike($uid, $vid, $pid)
    {
        return Notification::updateOrCreate([
            'type' => Notification::VIDEO_LIKE,
            'user_id' => $uid,
            'video_id' => $vid,
            'profile_id' => $pid,
        ]);
    }

    public static function newFollower($uid, $pid)
    {
        return Notification::updateOrCreate([
            'type' => Notification::NEW_FOLLOWER,
            'user_id' => $uid,
            'profile_id' => $pid,
        ]);
    }

    public static function unFollow($uid, $pid)
    {
        $res = Notification::where([
            'type' => Notification::NEW_FOLLOWER,
            'user_id' => $uid,
            'profile_id' => $pid,
        ])->first();
        if ($res) {
            $res->delete();
        }

    }

    public static function newVideoComment($pid, $uid, $vid, $cid)
    {
        return Notification::updateOrCreate([
            'type' => Notification::NEW_VIDCOMMENT,
            'user_id' => $uid,
            'profile_id' => $pid,
            'video_id' => $vid,
            'comment_id' => $cid,
        ]);
    }

    public static function newVideoCommentReply($pid, $uid, $vid, $cid, $crid)
    {
        return Notification::updateOrCreate([
            'type' => Notification::NEW_VIDCOMMENTREPLY,
            'user_id' => $uid,
            'profile_id' => $pid,
            'video_id' => $vid,
            'comment_id' => $cid,
            'comment_reply_id' => $crid,
        ]);
    }
}
