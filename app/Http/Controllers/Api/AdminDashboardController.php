<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CuratedApplicationApprovedMail;
use App\Mail\CuratedApplicationReceivedMail;
use App\Mail\CuratedApplicationRejectedMail;
use App\Mail\NewCuratedApplicationMail;
use App\Models\CuratedApplication;
use App\Models\CuratedApplicationSettings;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    protected AdminDashboardService $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $shouldRefresh = $request->has('refresh') && $request->boolean('refresh');
        $periodParam = $request->input('period', '30d');

        $data = $this->dashboardService->getDashboardData($periodParam, $shouldRefresh, $shouldRefresh);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function adminConfig(Request $request)
    {
        return response()->json(app(AdminDashboardService::class)->getConfigData());
    }

    public function curatedRejectedEmailPreviewWithReason(Request $request, $id)
    {
        $settings = CuratedApplicationSettings::current();

        $reason = collect($settings->rejection_reasons_list)->firstWhere('id', $id);

        if (! $reason) {
            abort(404, 'Rejection reason not found.');
        }

        $app = (new CuratedApplication)->forceFill([
            'id' => 0,
            'username_requested' => 'preview_user',
            'email' => 'preview@example.com',
            'status' => 'rejected',
        ]);

        if ($request->boolean('raw')) {
            $mailable = new CuratedApplicationRejectedMail($app, $reason['reason'], $settings->rejection_template);

            return response($mailable->render());
        }

        return view('admin.email-preview', [
            'backUrl' => url('/admin/curated-onboarding-settings'),
            'title' => 'Curated Rejection Email Preview — '.$reason['title'],
            'frameUrl' => route('admin.email-preview.curated-rejected-reason', ['id' => $id, 'raw' => true]),
        ]);
    }

    public function curatedRejectedEmailPreview(Request $request)
    {
        $app = (new CuratedApplication)->forceFill([
            'id' => 0,
            'username_requested' => 'preview_user',
            'email' => 'preview@example.com',
            'status' => 'rejected',
        ]);

        $settings = CuratedApplicationSettings::current();

        if ($request->boolean('raw')) {
            $mailable = new CuratedApplicationRejectedMail($app, 'reason here', $settings->rejection_template);

            return response($mailable->render());
        }

        return view('admin.email-preview', [
            'backUrl' => url('/admin/curated-onboarding-settings'),
            'title' => 'Curated Rejection Email Preview',
            'frameUrl' => route('admin.email-preview.curated-rejected', ['raw' => true]),
        ]);
    }

    public function curatedNotifyAdminEmailPreview(Request $request)
    {
        $token = Str::random(64);
        $token = hash('sha256', $token);
        $app = (new CuratedApplication)->forceFill([
            'id' => 0,
            'username_requested' => 'preview_user',
            'email' => 'preview@example.com',
            'status' => 'pending',
            'age_at_submission' => 24,
            'created_at' => now()->subDays(2),
            'email_verified_at' => now()->subHours(2),
            'email_verification_token' => $token,
        ]);

        if ($request->boolean('raw')) {
            $mailable = new NewCuratedApplicationMail($app);

            return response($mailable->render());
        }

        return view('admin.email-preview', [
            'backUrl' => url('/admin/curated-onboarding-settings'),
            'title' => 'Curated Notify Admins Verification Preview',
            'frameUrl' => route('admin.email-preview.curated-notify-admin', ['raw' => true]),
        ]);
    }

    public function curatedReceivedEmailPreview(Request $request)
    {
        $token = Str::random(64);
        $token = hash('sha256', $token);
        $app = (new CuratedApplication)->forceFill([
            'id' => 0,
            'username_requested' => 'preview_user',
            'email' => 'preview@example.com',
            'status' => 'pending',
            'email_verification_token' => $token,
        ]);

        if ($request->boolean('raw')) {
            $mailable = new CuratedApplicationReceivedMail($app);

            return response($mailable->render());
        }

        return view('admin.email-preview', [
            'backUrl' => url('/admin/curated-onboarding-settings'),
            'title' => 'Curated Received Email Verification Preview',
            'frameUrl' => route('admin.email-preview.curated-received', ['raw' => true]),
        ]);
    }

    public function curatedApprovalEmailPreview(Request $request)
    {
        $app = (new CuratedApplication)->forceFill([
            'id' => 0,
            'username_requested' => 'preview_user',
            'email' => 'preview@example.com',
            'status' => 'approved',
        ]);

        $magicLink = url('/auth/curated/complete?email='.$app->email.'&key='.Str::random(35));

        if ($request->boolean('raw')) {
            $mailable = new CuratedApplicationApprovedMail($app, $magicLink);

            return response($mailable->render());
        }

        return view('admin.email-preview', [
            'backUrl' => url('/admin/curated-onboarding-settings'),
            'title' => 'Curated Approval Email Preview',
            'frameUrl' => route('admin.email-preview.curated-approval', ['raw' => true]),
        ]);
    }
}
