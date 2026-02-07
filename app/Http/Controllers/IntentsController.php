<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Rules\ValidUsername;
use App\Services\ConfigService;
use App\Services\SanitizeService;
use App\Services\WebfingerService;
use Illuminate\Http\Request;

class IntentsController extends Controller
{
    use ApiHelpers;

    public function getFollowAccount(Request $request)
    {
        $config = app(ConfigService::class);

        if (! $config->federation()) {
            return abort(404, 'Not found');
        }

        $request->validate([
            'query' => ['required', 'string'],
        ]);

        $query = trim(urldecode($request->input('query')));
        $profile = null;

        if (preg_match('/^@?([\w.-]+)(?:@([\w.-]+\.\w+))?$/', $query, $matches)) {
            $username = $matches[1];
            $domain = $matches[2] ?? null;

            if (filter_var($query, FILTER_VALIDATE_URL)) {
                return $this->error('Invalid query format');
            }

            if ($domain) {
                if ($this->isDomainBanned($domain) || ! $this->isValidHostname($domain)) {
                    return $this->error('Invalid domain');
                }

                if (strtolower($domain) === $this->localDomain()) {
                    $profile = Profile::where('username', $username)
                        ->where('status', 1)
                        ->whereLocal(true)
                        ->first();
                } else {
                    $profile = Profile::where('username', $username.'@'.$domain)
                        ->where('status', 1)
                        ->whereLocal(false)
                        ->first();

                    if (! $profile) {
                        $profile = app(WebfingerService::class)->findOrCreateRemoteActor($username.'@'.$domain);
                        sleep(5);
                    }
                }
            } else {
                $validator = validator(['username' => $username], ['username' => new ValidUsername]);

                if ($validator->fails()) {
                    return $this->error('Invalid username format');
                }

                $profile = Profile::where('username', $username)
                    ->where('status', 1)
                    ->whereLocal(true)
                    ->first();
            }
        } else {
            $sanitizer = app(SanitizeService::class);
            $safeUrl = $sanitizer->url($query, verifyDns: true, bypassAppHost: true);

            if (! $safeUrl) {
                return $this->error('Invalid or unsafe URL');
            }

            $isLocal = $this->isLocalObject($safeUrl);

            if ($isLocal) {
                $profileMatch = $sanitizer->matchUrlTemplate(
                    url: $safeUrl,
                    templates: [
                        '/ap/users/{profileId}',
                        '/@{username}',
                        '/users/{username}',
                    ],
                    useAppHost: true,
                    constraints: [
                        'profileId' => '\d+',
                        'username' => '[a-zA-Z0-9][a-zA-Z0-9_.-]*[a-zA-Z0-9_]|[a-zA-Z0-9]',
                    ]
                );

                if ($profileMatch) {
                    if (isset($profileMatch['profileId'])) {
                        $profile = Profile::whereLocal(true)
                            ->whereStatus(1)
                            ->whereKey($profileMatch['profileId'])
                            ->first();
                    }

                    if (! $profile && isset($profileMatch['username'])) {
                        $username = $profileMatch['username'];
                        $validator = validator(['username' => $username], ['username' => new ValidUsername]);

                        if (! $validator->fails()) {
                            $profile = Profile::where('username', $username)
                                ->whereStatus(1)
                                ->whereLocal(true)
                                ->first();
                        }
                    }
                }
            } else {
                $profile = Profile::where('uri', $safeUrl)
                    ->orWhere('remote_url', $safeUrl)
                    ->where('status', 1)
                    ->first();

                if (! $profile) {
                    $profile = Profile::findOrCreateFromUrl($safeUrl);
                    sleep(5);
                }
            }
        }

        if (! $profile) {
            return $this->error('Profile not found');
        }

        return new ProfileResource($profile);
    }

    protected function isDomainBanned($domain): bool
    {
        return app(SanitizeService::class)->isDomainBanned($domain);
    }

    protected function isValidHostname($domain): bool
    {
        return app(SanitizeService::class)->isValidHostname($domain);
    }

    protected function localDomain(): string
    {
        $app = parse_url(config('app.url'));
        $appHost = strtolower(data_get($app, 'host'));

        return $appHost;
    }

    protected function isLocalObject(string $url): bool
    {
        $u = parse_url($url);

        if (! $u) {
            return false;
        }

        if ($u['scheme'] != 'https') {
            return false;
        }

        $host = strtolower(data_get($u, 'host'));
        $appHost = $this->localDomain();

        if ($host === $appHost) {
            return true;
        }

        return false;
    }
}
