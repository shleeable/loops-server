<?php

use App\Http\Controllers\AccountDataController;
use App\Http\Controllers\ActorController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\WebPublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailChangeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\InstanceActorController;
use App\Http\Controllers\NodeInfoController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SharedInboxController;
use App\Http\Controllers\UserRegisterVerifyController;
use App\Http\Controllers\WebfingerController;
use App\Http\Middleware\AdminOnlyAccess;
use App\Http\Middleware\AuthorizedFetch;
use App\Http\Middleware\FederationEnabled;
use Illuminate\Support\Facades\Route;

// NodeInfo endpoints
Route::group(['prefix' => 'nodeinfo'], function () {
    Route::get('2.0', [NodeInfoController::class, 'nodeInfo20'])
        ->name('nodeinfo.2.0')->middleware([FederationEnabled::class]);

    Route::get('2.1', [NodeInfoController::class, 'nodeInfo21'])
        ->name('nodeinfo.2.1')->middleware([FederationEnabled::class]);

    Route::any('{any}', function () {
        return response()->json(['message' => 'Not Found.'], 404);
    })->where('any', '.*');
});

Route::group(['prefix' => '.well-known'], function () {
    Route::get('host-meta', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
    <Link rel="lrdd" type="application/xrd+xml" template="'.url('/.well-known/webfinger').'?resource={uri}"/>
</XRD>';

        return response($xml, 200)->header('Content-Type', 'application/xrd+xml');
    })->name('host-meta')->middleware([FederationEnabled::class]);
    Route::get('nodeinfo', [NodeInfoController::class, 'discovery'])->middleware([FederationEnabled::class]);
    Route::get('webfinger', [WebfingerController::class, 'handle'])
        ->name('webfinger')->middleware([FederationEnabled::class]);

    Route::any('{any}', function () {
        return response()->json(['message' => 'Not Found.'], 404);
    })->where('any', '.*');
});

Route::prefix('api')->group(function () {
    Route::get('/v1/web/report-rules', [WebPublicController::class, 'reportTypes']);

    Route::get('/v1/platform/contact', [WebPublicController::class, 'getContactInfo']);
    Route::get('/v1/page/content', [WebPublicController::class, 'getPageContent']);

    Route::get('/v1/accounts/suggested', [AccountController::class, 'getSuggestedAccounts']);

    // Auth
    Route::post('/v1/auth/2fa/verify', [AuthController::class, 'verifyTwoFactor']);
    Route::post('/v1/auth/register/email', [UserRegisterVerifyController::class, 'sendEmailVerification']);
    Route::post('/v1/auth/register/email/resend', [UserRegisterVerifyController::class, 'resendEmailVerification']);
    Route::post('/v1/auth/register/email/verify', [UserRegisterVerifyController::class, 'verifyEmailVerification']);
    Route::post('/v1/auth/register/username', [UserRegisterVerifyController::class, 'claimUsername']);

    // Studio
    Route::get('/v1/upload/autocomplete/hashtag', [VideoController::class, 'getAutocompleteHashtag'])->middleware('auth:sanctum');
    Route::get('/v1/upload/autocomplete/mention', [VideoController::class, 'getAutocompleteMention'])->middleware('auth:sanctum');
    Route::get('/v1/studio/posts', [StudioController::class, 'getPosts'])->middleware('auth:sanctum');
    Route::post('/v1/studio/upload', [VideoController::class, 'store'])->middleware('auth:sanctum');

    // Search
    Route::post('/v1/search/users', [SearchController::class, 'get'])->middleware('auth:sanctum');

    // Feeds
    Route::get('/v0/user/self', [AccountController::class, 'selfAccountInfo'])->middleware('auth:sanctum');
    Route::get('/v0/feed/for-you', [FeedController::class, 'getForYouFeed'])->middleware('auth:sanctum');
    Route::get('/web/feed', [WebPublicController::class, 'getFeed']);

    // Accounts
    Route::get('/v1/account/info/self', [AccountController::class, 'selfAccountInfo'])->middleware('auth:sanctum');
    Route::get('/v1/account/info/{id}', [AccountController::class, 'getAccountInfo'])->middleware('auth:sanctum');
    Route::get('/v1/account/state/{id}', [AccountController::class, 'getRelationshipState'])->middleware('auth:sanctum');
    Route::get('/v1/account/username/{id}', [WebPublicController::class, 'getAccountInfoByUsername']);
    Route::post('/v1/account/block/{id}', [AccountController::class, 'accountBlock'])->middleware('auth:sanctum');
    Route::post('/v1/account/unblock/{id}', [AccountController::class, 'accountUnblock'])->middleware('auth:sanctum');
    Route::get('/v1/account/followers/{id}', [WebPublicController::class, 'accountFollowers']);
    Route::get('/v1/account/following/{id}', [WebPublicController::class, 'accountFollowing']);
    Route::post('/v1/account/follow/{id}', [AccountController::class, 'follow'])->middleware('auth:sanctum');
    Route::post('/v1/account/unfollow/{id}', [AccountController::class, 'unfollow'])->middleware('auth:sanctum');
    Route::post('/v1/account/undo-follow-request/{id}', [AccountController::class, 'undoFollowRequest'])->middleware('auth:sanctum');

    // Notifications
    Route::get('/v1/account/notifications', [AccountController::class, 'notifications'])->middleware('auth:sanctum');
    Route::get('/v1/account/notifications/count', [AccountController::class, 'notificationUnreadCount'])->middleware('auth:sanctum');
    Route::post('/v1/account/notifications/mark-all-read', [AccountController::class, 'markAllNotificationsAsRead'])->middleware('auth:sanctum');
    Route::post('/v1/account/notifications/{id}/read', [AccountController::class, 'markNotificationAsRead'])->middleware('auth:sanctum');

    // Settings
    Route::post('/v1/account/settings/bio', [SettingsController::class, 'storeBio'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/update-avatar', [SettingsController::class, 'updateAvatar'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/delete-avatar', [SettingsController::class, 'deleteAvatar'])->middleware('auth:sanctum');
    Route::get('/v1/account/settings/security-config', [SettingsController::class, 'securityConfig'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/update-password', [SettingsController::class, 'updatePassword'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/disable-2fa', [SettingsController::class, 'disableTwoFactor'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/setup-2fa', [SettingsController::class, 'setupTwoFactor'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/confirm-2fa', [SettingsController::class, 'confirmTwoFactor'])->middleware('auth:sanctum');
    Route::get('/v1/account/settings/total-blocked-accounts', [SettingsController::class, 'totalBlockedAccounts'])->middleware('auth:sanctum');
    Route::get('/v1/account/settings/blocked-accounts', [SettingsController::class, 'blockedAccounts'])->middleware('auth:sanctum');
    Route::post('/v1/account/settings/blocked-account-search', [SettingsController::class, 'blockedAccountSearch'])->middleware(['auth:sanctum', 'throttle:autocomplete']);
    Route::get('/v1/account/settings/email', [EmailChangeController::class, 'getEmailSettings'])->middleware(['auth:sanctum']);
    Route::post('/v1/account/settings/email/update', [EmailChangeController::class, 'changeEmail'])->middleware(['auth:sanctum']);
    Route::post('/v1/account/settings/email/cancel', [EmailChangeController::class, 'cancelEmailChange'])->middleware(['auth:sanctum']);
    Route::post('/v1/account/settings/email/verify', [EmailChangeController::class, 'verifyEmailChange'])->middleware(['auth:sanctum']);
    Route::post('/v1/account/settings/email/resend', [EmailChangeController::class, 'resendEmailChange'])->middleware(['auth:sanctum']);

    // Account Data
    Route::get('/v1/account/data/insights', [AccountDataController::class, 'getDataInsights'])->middleware(['auth:sanctum']);
    Route::get('/v1/account/data/settings', [AccountDataController::class, 'getDataSettings'])->middleware(['auth:sanctum']);
    Route::put('/v1/account/data/settings', [AccountDataController::class, 'updateDataSettings'])->middleware(['auth:sanctum']);
    Route::post('/v1/account/data/export/full', [AccountDataController::class, 'requestFullExport'])->name('export.full')->middleware(['auth:sanctum']);
    Route::post('/v1/account/data/export/selective', [AccountDataController::class, 'requestSelectiveExport'])->name('export.selective')->middleware(['auth:sanctum']);
    Route::get('/v1/account/data/export/history', [AccountDataController::class, 'getExportHistory'])->name('export.history')->middleware(['auth:sanctum']);
    Route::get('/v1/account/data/export/{id}/download', [AccountDataController::class, 'downloadExport'])->name('export.download')->middleware(['auth:sanctum']);

    // Account Feeds
    Route::get('/v1/feed/account/self', [FeedController::class, 'selfAccountFeed'])->middleware('auth:sanctum');
    Route::get('/v1/feed/account/{id}', [WebPublicController::class, 'getAccountFeed']);

    // Explore Feed
    Route::get('/v1/explore/tags', [ExploreController::class, 'getTrendingTags']);
    Route::get('/v1/explore/tag-feed/{id}', [ExploreController::class, 'getTagFeed']);

    Route::get('/v1/tags/video/{id}', [WebPublicController::class, 'getVideoTags']);

    // Global Feeds
    Route::get('/v1/feed/for-you', [FeedController::class, 'getForYouFeed'])->middleware('auth:sanctum');
    Route::get('/v1/feed/following', [FeedController::class, 'getFollowingFeed'])->middleware('auth:sanctum');

    // Video Comments
    Route::post('/v1/comments/like/{vid}/{id}', [VideoController::class, 'storeCommentLike'])->middleware('auth:sanctum');
    Route::post('/v1/comments/like/{vid}/{pid}/{id}', [VideoController::class, 'storeCommentReplyLike'])->middleware('auth:sanctum');
    Route::post('/v1/comments/unlike/{vid}/{pid}/{id}', [VideoController::class, 'storeCommentReplyUnlike'])->middleware('auth:sanctum');
    Route::post('/v1/comments/unlike/{vid}/{id}', [VideoController::class, 'storeCommentUnlike'])->middleware('auth:sanctum');
    Route::post('/v1/comments/delete/{vid}/{id}', [VideoController::class, 'deleteComment'])->middleware('auth:sanctum');
    Route::post('/v1/comments/delete/{vid}/{pid}/{id}', [VideoController::class, 'deleteCommentReply'])->middleware('auth:sanctum');
    Route::post('/v1/video/comments/reply/edit/{id}', [VideoController::class, 'storeCommentReplyUpdate'])->middleware('auth:sanctum');
    Route::post('/v1/video/comments/edit/{id}', [VideoController::class, 'storeCommentUpdate'])->middleware('auth:sanctum');
    Route::post('/v1/video/comments/{id}', [VideoController::class, 'storeComment'])->middleware('auth:sanctum');
    Route::get('/v1/video/comments/{id}', [WebPublicController::class, 'comments']);
    Route::get('/v1/video/comments/{vid}/replies', [WebPublicController::class, 'commentsThread']);
    Route::get('/v1/comments/history/{vid}/{cid}', [VideoController::class, 'showCommentsHistory']);
    Route::get('/v1/comments/history/{vid}/{cid}/{id}', [VideoController::class, 'showCommentReplyHistory']);
    Route::get('/v1/video/comments/{videoId}/comment/{commentId}', [WebPublicController::class, 'getCommentById']);
    Route::get('/v1/video/comments/{videoId}/reply/{replyId}', [WebPublicController::class, 'getReplyById']);

    // Videos
    Route::post('/v1/video/edit/{id}', [VideoController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/v1/video/delete/{id}', [VideoController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('/v1/video/like/{id}', [VideoController::class, 'like'])->middleware('auth:sanctum');
    Route::post('/v1/video/unlike/{id}', [VideoController::class, 'unlike'])->middleware('auth:sanctum');
    Route::get('/v1/video/history/{id}', [VideoController::class, 'showVideoHistory']);
    Route::get('/v1/video/{id}', [WebPublicController::class, 'showVideo']);

    // Autocomplete
    Route::get('/v1/autocomplete/tags', [VideoController::class, 'showAutocompleteTags'])->middleware('auth:sanctum');
    Route::get('/v1/autocomplete/accounts', [VideoController::class, 'showAutocompleteAccounts'])->middleware('auth:sanctum');

    // Reports
    Route::post('/v1/report', [ReportController::class, 'store'])->middleware('auth:sanctum');

    // Admin
    Route::prefix('/v1/admin')->middleware(['auth:sanctum', AdminOnlyAccess::class])->group(function () {

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('pages', [PageController::class, 'index']);
            Route::post('pages', [PageController::class, 'store']);
            Route::get('pages/{id}', [PageController::class, 'show']);
            Route::put('pages/{id}', [PageController::class, 'update']);
            Route::delete('pages/{id}', [PageController::class, 'destroy']);
        });

        Route::get('/videos', [AdminController::class, 'videos'])->middleware('auth:sanctum');
        Route::get('/comments', [AdminController::class, 'comments'])->middleware('auth:sanctum');
        Route::get('/videos/{id}/comments', [AdminController::class, 'videoCommentsShow'])->middleware('auth:sanctum');
        Route::post('/comments/{id}/delete', [AdminController::class, 'videoCommentsDelete'])->middleware('auth:sanctum');
        Route::post('/videos/{id}/moderate', [AdminController::class, 'videoModerate'])->middleware('auth:sanctum');
        Route::get('/video/{id}', [AdminController::class, 'videoShow'])->middleware('auth:sanctum');
        Route::get('/profiles', [AdminController::class, 'profiles'])->middleware('auth:sanctum');
        Route::get('/hashtags', [AdminController::class, 'hashtags'])->middleware('auth:sanctum');
        Route::post('/hashtags/{id}/update', [AdminController::class, 'hashtagsUpdate'])->middleware('auth:sanctum');
        Route::get('/reports', [AdminController::class, 'reports'])->middleware('auth:sanctum');
        Route::get('/reports/{id}', [AdminController::class, 'reportShow'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/update-admin-notes', [AdminController::class, 'reportUpdateAdminNotes'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/mark-nsfw', [AdminController::class, 'reportUpdateMarkAsNsfw'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/comment-delete', [AdminController::class, 'reportDeleteComment'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/comment-reply-delete', [AdminController::class, 'reportDeleteCommentReply'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/dismiss', [AdminController::class, 'reportDismiss'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/dismiss-all-by-account', [AdminController::class, 'reportDismissAllByAccount'])->middleware('auth:sanctum');
        Route::post('/reports/{id}/video-delete', [AdminController::class, 'reportDeleteVideo'])->middleware('auth:sanctum');
        Route::get('/profiles/{id}', [AdminController::class, 'profileShow'])->middleware('auth:sanctum');
        Route::post('/profiles/{id}/permissions', [AdminController::class, 'profilePermissionUpdate'])->middleware('auth:sanctum');
        Route::post('/profiles/{id}/admin_note', [AdminController::class, 'profileAdminNoteUpdate'])->middleware('auth:sanctum');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->middleware('auth:sanctum');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->middleware('auth:sanctum');
        Route::post('/settings/update-logo', [AdminSettingsController::class, 'updateLogo'])->middleware('auth:sanctum');
        Route::post('/settings/delete-logo', [AdminSettingsController::class, 'deleteLogo'])->middleware('auth:sanctum');
        Route::get('/instances', [AdminController::class, 'instances'])->middleware('auth:sanctum');
        Route::get('/instances/stats', [AdminController::class, 'instanceStats'])->middleware('auth:sanctum');
        Route::post('/instances/create', [AdminController::class, 'instanceCreate'])->middleware('auth:sanctum');
        Route::post('/instances/bulk-create', [AdminController::class, 'instanceBulkCreate'])->middleware('auth:sanctum');
        Route::get('/instances/{id}', [AdminController::class, 'instanceShow'])->middleware('auth:sanctum');
        Route::get('/instances/{id}/users', [AdminController::class, 'instanceShowUsers'])->middleware('auth:sanctum');
        Route::get('/instances/{id}/videos', [AdminController::class, 'instanceShowVideos'])->middleware('auth:sanctum');
        Route::get('/instances/{id}/comments', [AdminController::class, 'instanceShowComments'])->middleware('auth:sanctum');
        Route::post('/instances/{id}/update-admin-notes', [AdminController::class, 'updateInstanceAdminNotes'])->middleware('auth:sanctum');
        Route::post('/instances/{id}/settings', [AdminController::class, 'updateInstanceSettings'])->middleware('auth:sanctum');
        Route::get('/instances/{id}/reports', [AdminController::class, 'showInstanceReports'])->middleware('auth:sanctum');
        Route::post('/instances/{id}/refresh', [AdminController::class, 'updateInstanceRefreshData'])->middleware('auth:sanctum');
        Route::post('/instances/{id}/suspend', [AdminController::class, 'instanceSuspend'])->middleware('auth:sanctum');
        Route::post('/instances/{id}/activate', [AdminController::class, 'instanceActivate'])->middleware('auth:sanctum');
    });

    Route::any('{any}', function () {
        return response()->json(['message' => 'Not Found.'], 404);
    })->where('any', '.*');
});

Route::prefix('/ap')->middleware([AuthorizedFetch::class])->group(function () {
    Route::get('actor', [InstanceActorController::class, 'show'])->middleware(FederationEnabled::class);
    Route::post('inbox', [SharedInboxController::class, 'sharedInbox']);
    Route::post('actor/inbox', [SharedInboxController::class, 'sharedInbox']);
    Route::get('actor/outbox', [InstanceActorController::class, 'outbox'])->middleware(FederationEnabled::class);
    Route::get('users/{actor}', [ActorController::class, 'show'])->middleware(AuthorizedFetch::class);
    Route::post('users/{actor}/inbox', [InboxController::class, 'userInbox']);
    Route::get('users/{actor}/outbox', [ActorController::class, 'outbox'])->middleware(AuthorizedFetch::class);
    Route::get('users/{actor}/followers', [ActorController::class, 'followers'])->middleware(AuthorizedFetch::class);
    Route::get('users/{actor}/following', [ActorController::class, 'following'])->middleware(AuthorizedFetch::class);
    Route::get('users/{actor}/video/{id}', [ObjectController::class, 'showVideoObject'])->middleware(AuthorizedFetch::class);
    Route::get('users/{actor}/comment/{id}', [ObjectController::class, 'showCommentObject'])->middleware(AuthorizedFetch::class);
    Route::get('users/{actor}/reply/{id}', [ObjectController::class, 'showReplyObject'])->middleware(AuthorizedFetch::class);
});
