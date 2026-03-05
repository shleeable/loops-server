<?php

namespace App\Http\Controllers;

use App\Http\Middleware\FederationEnabled;
use App\Http\Middleware\HasHttpSignature;
use App\Jobs\Federation\ProcessInboxActivityWithVerification;
use Illuminate\Http\Request;

class SharedInboxController extends Controller
{
    public function __construct()
    {
        $this->middleware(FederationEnabled::class);
        $this->middleware(HasHttpSignature::class);
    }

    public function sharedInbox(Request $request)
    {
        $activity = $request->json()->all();

        $headers = $request->attributes->get('raw_headers') ?? $request->headers->all();

        ProcessInboxActivityWithVerification::dispatch(
            $activity,
            $headers,
            $request->method(),
            $request->getRequestUri(),
            $request->getContent(),
            null,
            false
        )->onQueue('activitypub-in');

        return response()->json(['status' => 'accepted'], 202);
    }
}
