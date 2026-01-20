<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Models\UserInterest;
use App\Models\VideoBookmark;
use App\Models\VideoLike;
use App\Services\ForYouFeedService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserInterestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $profileId
    ) {}

    public function handle(): void
    {
        $profile = Profile::find($this->profileId);

        if (! $profile) {
            return;
        }

        $this->updateFromLikes($profile);

        $this->updateFromBookmarks($profile);

        $this->decayInterests($profile);

        app(ForYouFeedService::class)->forgetUserInterests($this->profileId);
    }

    private function updateFromLikes(Profile $profile): void
    {
        $recentLikes = VideoLike::where('profile_id', $profile->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('video.profile')
            ->limit(100)
            ->get();

        foreach ($recentLikes as $like) {
            if (! $like->video) {
                continue;
            }

            $this->upsertInterest(
                $profile->id,
                UserInterest::TYPE_CREATOR,
                (string) $like->video->profile_id,
                1.0
            );

            foreach ($like->video->hashtags as $hashtag) {
                $this->upsertInterest(
                    $profile->id,
                    UserInterest::TYPE_HASHTAG,
                    $hashtag->name,
                    1.0
                );
            }

            // if ($like->video->category) {
            //     $this->upsertInterest(
            //         $profile->id,
            //         UserInterest::TYPE_CATEGORY,
            //         $like->video->category,
            //         1.0
            //     );
            // }
        }
    }

    private function updateFromBookmarks(Profile $profile): void
    {
        $recentBookmarks = VideoBookmark::where('profile_id', $profile->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('video.profile')
            ->limit(50)
            ->get();

        foreach ($recentBookmarks as $bookmark) {
            if (! $bookmark->video) {
                continue;
            }

            $this->upsertInterest(
                $profile->id,
                UserInterest::TYPE_CREATOR,
                (string) $bookmark->video->profile_id,
                1.5
            );

            foreach ($bookmark->video->hashtags as $hashtag) {
                $this->upsertInterest(
                    $profile->id,
                    UserInterest::TYPE_HASHTAG,
                    $hashtag->name,
                    1.5
                );
            }

            if ($bookmark->video->category) {
                $this->upsertInterest(
                    $profile->id,
                    UserInterest::TYPE_CATEGORY,
                    $bookmark->video->category,
                    1.5
                );
            }
        }
    }

    private function upsertInterest(
        int $profileId,
        string $type,
        string $value,
        float $weight
    ): void {
        $interest = UserInterest::firstOrCreate(
            [
                'profile_id' => $profileId,
                'interest_type' => $type,
                'interest_value' => $value,
            ],
            [
                'score' => 0,
                'interaction_count' => 0,
            ]
        );

        $interest->recordInteraction($weight);
    }

    private function decayInterests(Profile $profile): void
    {
        $interests = UserInterest::where('profile_id', $profile->id)
            ->where('last_interaction_at', '<', now()->subDays(7))
            ->get();

        foreach ($interests as $interest) {
            $interest->decay(0.95);

            if ($interest->score < 1) {
                $interest->delete();
            }
        }
    }
}
