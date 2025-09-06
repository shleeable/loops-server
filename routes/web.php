<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/{vue?}', 'welcome')->where('vue', '.*');

Auth::routes([
    'login' => true,
    'logout' => true,
    'register' => false,
    'reset' => true,
    'confirm' => false,
    'verify' => false,
]);
