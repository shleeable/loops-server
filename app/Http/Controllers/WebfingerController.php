<?php

namespace App\Http\Controllers;

use App\Services\WebfingerService;
use Illuminate\Http\Request;

class WebfingerController extends Controller
{
    protected $webfingerService;

    public function __construct(WebfingerService $webfingerService)
    {
        $this->webfingerService = $webfingerService;
    }

    /**
     * Handle webfinger requests
     *
     * GET /.well-known/webfinger?resource=acct:username@domain.com
     */
    public function handle(Request $request)
    {
        $resource = $request->query('resource');

        if (! $resource) {
            return response()->json([
                'error' => 'Missing resource parameter',
            ], 400);
        }

        $result = $this->webfingerService->lookupLocal($resource);

        if (! $result) {
            return response()->json([
                'error' => 'Resource not found',
            ], 404);
        }

        return response()->json($result)
            ->header('Content-Type', 'application/jrd+json; charset=utf-8')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
