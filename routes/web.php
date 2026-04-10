<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\WebPublicController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\ProfileLinkRedirectController;
use App\Http\Middleware\AdminOnlyAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/u/{profileId}', [WebPublicController::class, 'userPermalinkRedirect']);
Route::get('/r/pl/{pid}/{lid}', [ProfileLinkRedirectController::class, 'redirect']);

Route::get('/admin/curated-onboarding-settings/mail/preview/notify-admin', [AdminDashboardController::class, 'curatedNotifyAdminEmailPreview'])->middleware(['auth:web,api', AdminOnlyAccess::class])->name('admin.email-preview.curated-notify-admin');
Route::get('/admin/curated-onboarding-settings/mail/preview/received', [AdminDashboardController::class, 'curatedReceivedEmailPreview'])->middleware(['auth:web,api', AdminOnlyAccess::class])->name('admin.email-preview.curated-received');
Route::get('/admin/curated-onboarding-settings/mail/preview/approved', [AdminDashboardController::class, 'curatedApprovalEmailPreview'])->middleware(['auth:web,api', AdminOnlyAccess::class])->name('admin.email-preview.curated-approval');
Route::get('/admin/curated-onboarding-settings/mail/preview/rejected/{id}', [AdminDashboardController::class, 'curatedRejectedEmailPreviewWithReason'])->middleware(['auth:web,api', AdminOnlyAccess::class])->name('admin.email-preview.curated-rejected-reason');
Route::get('/admin/curated-onboarding-settings/mail/preview/rejected', [AdminDashboardController::class, 'curatedRejectedEmailPreview'])->middleware(['auth:web,api', AdminOnlyAccess::class])->name('admin.email-preview.curated-rejected');

Route::get('/starter-kits/{hid}/{slug}', [ObjectController::class, 'showStarterKitRedirect']);
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
