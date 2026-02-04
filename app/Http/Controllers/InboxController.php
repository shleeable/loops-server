<?php

namespace App\Http\Controllers;

use App\Http\Middleware\FederationEnabled;
use App\Http\Middleware\HasHttpSignature;
use App\Jobs\Federation\ProcessInboxActivityWithVerification;
use App\Models\Profile;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __construct()
    {
        $this->middleware(FederationEnabled::class);
        $this->middleware(HasHttpSignature::class);
    }

    public function userInbox(Request $request, Profile $actor)
    {
        if (! $actor->local) {
            abort(404);
        }

        $activity = $request->json()->all();

        if (isset($activity['type']) && $activity['type'] === 'Delete') {
            if (! $this->validateDeleteOrigin($activity, $request)) {
                return response()->json(['status' => 'accepted'], 202);
            }
        }

        $headers = collect($request->headers->all())->mapWithKeys(function ($value, $key) {
            return [strtolower($key) => $value];
        })->toArray();

        ProcessInboxActivityWithVerification::dispatch(
            $activity,
            $headers,
            $request->method(),
            $request->getRequestUri(),
            $request->getContent(),
            $actor,
            true
        )->onQueue('activitypub-in');

        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Validate that a Delete activity's object matches the signed Host header
     */
    protected function validateDeleteOrigin(array $activity, Request $request): bool
    {
        if (! isset($activity['object'])) {
            return false;
        }

        $objectUrl = is_array($activity['object'])
            ? data_get($activity, 'object.id', null)
            : data_get($activity, 'object', null);

        if (! $objectUrl) {
            return false;
        }

        $signedOriginDomain = $request->attributes->get('activitypub_origin_domain');

        if (! $signedOriginDomain) {
            return false;
        }

        $objectDomain = parse_url($objectUrl, PHP_URL_HOST);

        if (! $objectDomain) {
            return false;
        }

        return strtolower($signedOriginDomain) === strtolower($objectDomain);
    }
}
