<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\WebPublicController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\EmailChangeController;
use App\Http\Controllers\AccountDataController;
use App\Http\Controllers\UserRegisterVerifyController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\AdminOnlyAccess;

Route::get('/v1/web/report-rules', [WebPublicController::class, 'reportTypes']);

Route::get('/v1/platform/contact', [WebPublicController::class, 'getContactInfo']);
Route::get('/v1/page/content', [WebPublicController::class, 'getPageContent']);

Route::get('/v1/accounts/suggested',[AccountController::class, 'getSuggestedAccounts']);

