<?php

namespace App\Concerns;

use App\Services\SanitizeService;

trait HasSyncMentionsFromCaption
{
    public function syncMentionsFromCaption()
    {
        if (! $this->caption) {
            $this->mentions()->delete();

            return;
        }

        $this->mentions()->delete();
        $extractedMentions = app(SanitizeService::class)->extractMentions($this->caption);

        $existingProfileIds = $this->mentions()->pluck('profile_id')->toArray();
        $newProfileIds = array_column($extractedMentions, 'profile_id');

        $this->mentions()
            ->whereNotIn('profile_id', $newProfileIds)
            ->delete();

        foreach ($extractedMentions as $mention) {
            if (! in_array($mention['profile_id'], $existingProfileIds)) {
                $this->mentions()->create([
                    'profile_id' => $mention['profile_id'],
                    'username' => $mention['username'],
                    'start_index' => $mention['start_index'],
                    'end_index' => $mention['end_index'],
                    'is_local' => $mention['is_local'],
                ]);
            }
        }
    }
}
