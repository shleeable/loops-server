<?php

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $processedCount = 0;
        $notificationCount = 0;
        $affectedProfiles = [];

        DB::table('video_reposts')
            ->select([
                'video_reposts.id',
                'video_reposts.profile_id as sharer_id',
                'video_reposts.video_id',
                'video_reposts.created_at',
                'videos.profile_id as owner_id',
            ])
            ->join('videos', 'video_reposts.video_id', '=', 'videos.id')
            ->join('profiles as sharers', 'video_reposts.profile_id', '=', 'sharers.id')
            ->join('profiles as owners', 'videos.profile_id', '=', 'owners.id')
            ->where('videos.status', 2)
            ->where('sharers.status', 1)
            ->where('owners.status', 1)
            ->whereColumn('video_reposts.profile_id', '!=', 'videos.profile_id')
            ->orderBy('video_reposts.id')
            ->chunk(500, function ($reposts) use (&$processedCount, &$notificationCount, &$affectedProfiles) {
                $chunkAffectedProfiles = [];

                foreach ($reposts as $repost) {
                    $exists = DB::table('notifications')
                        ->where('type', Notification::VIDEO_SHARE)
                        ->where('user_id', $repost->owner_id)
                        ->where('video_id', $repost->video_id)
                        ->where('profile_id', $repost->sharer_id)
                        ->exists();

                    if (! $exists) {
                        DB::table('notifications')->insert([
                            'type' => Notification::VIDEO_SHARE,
                            'user_id' => $repost->owner_id,
                            'video_id' => $repost->video_id,
                            'profile_id' => $repost->sharer_id,
                            'created_at' => $repost->created_at,
                            'updated_at' => $repost->created_at,
                        ]);

                        $notificationCount++;
                        $chunkAffectedProfiles[$repost->owner_id] = true;
                    }

                    $processedCount++;
                }

                foreach (array_keys($chunkAffectedProfiles) as $profileId) {
                    NotificationService::clearUnreadCount($profileId);
                    $affectedProfiles[$profileId] = true;
                }
            });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'created_at'], 'notifications_user_type_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_type_created_idx');
        });
    }
};
