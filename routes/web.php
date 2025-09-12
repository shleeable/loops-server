<?php

use App\Http\Controllers\NodeInfoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '.well-known'], function () {
    Route::get('nodeinfo', [NodeInfoController::class, 'discovery']);
});

Route::view('/{vue?}', 'welcome')->where('vue', '.*');

Auth::routes([
    'login' => true,
    'logout' => true,
    'register' => false,
    'reset' => true,
    'confirm' => false,
    'verify' => false,
]);
