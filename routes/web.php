<?php

use App\Http\Controllers\Auth\AuthorizationController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\ProfileLinkRedirectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers;

Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])
    ->middleware('web')
    ->name('passport.authorizations.authorize');

Route::post('/oauth/token', [Controllers\AccessTokenController::class, 'issueToken'])
    ->middleware('throttle')
    ->name('passport.token');

Route::middleware(['web', 'auth:web'])->group(function () {
    Route::post('/oauth/authorize', [Controllers\ApproveAuthorizationController::class, 'approve'])
        ->name('passport.authorizations.approve');

    Route::delete('/oauth/authorize', [Controllers\DenyAuthorizationController::class, 'deny'])
        ->name('passport.authorizations.deny');

    Route::post('/oauth/token/refresh', [Controllers\TransientTokenController::class, 'refresh'])
        ->name('passport.token.refresh');
});

Route::get('/r/pl/{pid}/{lid}', [ProfileLinkRedirectController::class, 'redirect']);

Route::get('v/{hashId}', [ObjectController::class, 'showVideo']);
Route::get('@{username}', [ObjectController::class, 'showProfile']);

Route::view('/{vue?}', 'welcome')->where('vue', '.*');

Auth::routes([
    'login' => true,
    'logout' => true,
    'register' => false,
    'reset' => true,
    'confirm' => false,
    'verify' => false,
]);
