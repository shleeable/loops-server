<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Models\User;
use App\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class AccountDisableJob implements ShouldQueue
{
    use Queueable;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;

        DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();
        DB::table('oauth_auth_codes')->where('user_id', $user->id)->delete();
        Video::whereProfileId($user->profile_id)->update(['status' => 7]);
        $profile = Profile::find($user->profile_id);
        $profile->status = 7;
        $profile->save();
        $userModel = User::find($user->id);
        $userModel->status = 7;
        $userModel->save();
    }
}
