<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountService;
use App\Services\AtomFeedService;
use App\Services\ConfigService;
use Illuminate\Support\Facades\Request;

class AtomFeedController extends Controller
{
    public function show(Request $request, $profileId)
    {
        abort_unless(app(ConfigService::class)->atomFeeds(), 404);

        $user = User::isActive()->where('profile_id', $profileId)->firstOrFail();

        abort_unless($user->has_atom, 404);

        $videos = AtomFeedService::getFeed($profileId);
        $account = AccountService::compact($user->profile_id, true);

        abort_if(is_null($videos) || empty($account), 404);

        $videos = collect($videos);
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);

        return response()
            ->view('feed.atom', compact('user', 'videos', 'appUrl', 'account'))
            ->header('Content-Type', 'application/atom+xml');
    }
}
