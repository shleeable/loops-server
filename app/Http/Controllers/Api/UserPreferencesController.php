<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use App\Services\UserAppPreferencesService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPreferencesController extends Controller
{
    use ApiHelpers;

    public function __construct(
        protected UserAppPreferencesService $preferencesService
    ) {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $preferences = $this->preferencesService->get($user->id);
        $res = [
            'account' => ['username' => $user->username, 'profile_id' => (string) $user->profile_id],
            'settings' => $preferences,
        ];

        return $this->data($res);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'autoplay_videos' => 'boolean',
            'loop_videos' => 'boolean',
            'default_feed' => [
                'string',
                Rule::in(['local', 'following', 'forYou']),
                function ($attribute, $value, $fail) {
                    if ($value === 'forYou' && ! app(ConfigService::class)->forYouFeed()) {
                        $fail('The For You feed is not available on this server.');
                    }
                },
            ],
            'hide_for_you_feed' => 'boolean',
            'mute_on_open' => 'boolean',
            'auto_expand_cw' => 'boolean',
            'appearance' => 'in:light,dark,system',
        ]);

        $user = $request->user();

        abort_if($user->status != 1, 403, 'You do not have permission for this action.');

        $preferences = $this->preferencesService->update(
            $user->id,
            $validated
        );

        $res = [
            'account' => ['username' => $user->username, 'profile_id' => (string) $user->profile_id],
            'settings' => $preferences,
        ];

        return $this->data($res);
    }
}
