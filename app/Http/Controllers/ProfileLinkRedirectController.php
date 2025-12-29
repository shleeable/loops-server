<?php

namespace App\Http\Controllers;

use App\Models\ProfileLink;
use App\Services\HashidService;
use Illuminate\Http\RedirectResponse;

class ProfileLinkRedirectController extends Controller
{
    public function redirect(string $pid, string $lid): RedirectResponse
    {
        $profileId = HashidService::safeDecode($pid);
        $id = HashidService::safeDecode($lid);

        $link = ProfileLink::findOrFail($id);
        abort_if($profileId != $link->profile_id, 404);

        $link->incrementClickCount();

        return redirect()->away($link->url);
    }
}
