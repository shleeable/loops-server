<?php

use App\Http\Controllers\ObjectController;
use App\Http\Controllers\ProfileLinkRedirectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
