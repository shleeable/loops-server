<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            $request->user()->must_change_password
        ) {
            $allowed = [
                'password.change',
                'password.update',
                'logout',
            ];

            if (! in_array($request->route()?->getName(), $allowed)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You must change your password before continuing.',
                        'redirect' => '/settings/password',
                    ], 403);
                }

                return redirect()->route('password.change')
                    ->with('warning', 'Please set a new password to continue.');
            }
        }

        return $next($request);
    }
}
