<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
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
        if($res) {
            $res->delete();
        }
        return;
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

    public static function create($uid, $type, $itemClass, $itemId, $meta = false)
    {
        $n = new Notification;
        $n->user_id = $uid;
        $n->type = $type;
        $n->item_type = $itemClass;
        $n->item_id = $itemId;

        if($meta) {
            $n->meta = $meta;
        }

        $n->save();

        return $n;
    }
}
