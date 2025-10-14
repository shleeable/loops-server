<?php

namespace App\Http\Controllers;

use App\Services\InstanceActorService;
use Illuminate\Http\Request;

class InstanceActorController extends Controller
{
    public function show(Request $request)
    {
        $actor = app(InstanceActorService::class)->getActor();

        return $this->activityPubResponse($actor);
    }

    public function outbox(Request $request)
    {
        $actor = app(InstanceActorService::class)->getActorOutbox();

        return $this->activityPubResponse($actor);
    }
}
