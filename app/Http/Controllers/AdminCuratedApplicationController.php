<?php

namespace App\Http\Controllers;

use App\Http\Resources\CuratedApplicationResource;
use App\Models\CuratedApplication;
use App\Models\CuratedApplicationNote;
use App\Models\CuratedApplicationSettings;
use App\Services\AdminDashboardService;
use App\Services\CuratedOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCuratedApplicationController extends Controller
{
    public function __construct(
        private CuratedOnboardingService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $query = CuratedApplication::query()
            ->withCount('notes')
            ->with('reviewer');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->boolean('verified_only')) {
            $query->emailVerified();
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('username_requested', 'like', "%{$search}%")
                    ->orWhere('fediverse_account', 'like', "%{$search}%");
            });
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['created_at', 'age_at_submission', 'status', 'email'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $applications = $query->paginate($request->input('limit', 25));

        return CuratedApplicationResource::collection($applications)
            ->response();
    }

    public function show(CuratedApplication $application): JsonResponse
    {
        $this->authorize('view', $application);

        $application->load(['reviewer', 'notes']);

        $settings = CuratedApplicationSettings::current();
        $questions = collect($settings->questions_list)->keyBy('id');

        $customQa = [];
        if ($application->custom_answers) {
            foreach ($application->custom_answers as $questionId => $answer) {
                $question = $questions->get($questionId);
                $customQa[] = [
                    'id' => $questionId,
                    'label' => $question['label'] ?? 'Unknown question',
                    'type' => $question['type'] ?? 'text',
                    'answer' => $answer,
                ];
            }
        }

        if ($settings->rejection_reasons) {
            $application->setAttribute('custom_rejections', $settings->rejection_reasons);
        }

        $application->setAttribute('custom_questions', $customQa);

        return response()->json(new CuratedApplicationResource($application));
    }

    public function deleteNote(CuratedApplication $application, $noteId): JsonResponse
    {
        $this->authorize('delete', $application);

        $note = CuratedApplicationNote::where('application_id', $application->id)->findOrFail($noteId);
        $note->delete();
        $application->refresh();
        $application->load(['reviewer', 'notes']);

        return response()->json(new CuratedApplicationResource($application));
    }

    public function deleteApplication(CuratedApplication $application): JsonResponse
    {
        $application->delete();

        return response()->json([]);
    }

    public function approve(Request $request, CuratedApplication $application): JsonResponse
    {
        $this->authorize('approve', $application);

        $application = $this->service->approve($application, $request->user());

        $this->clearStats();

        return response()->json([
            'message' => 'Application approved. Account creation is being processed.',
            'data' => new CuratedApplicationResource($application),
        ]);
    }

    public function forceApprove(Request $request, CuratedApplication $application): JsonResponse
    {
        $application->update(['email_verified_at' => now(), 'status' => 'ready']);

        $this->authorize('approve', $application->refresh());

        $application = $this->service->approve($application->refresh(), $request->user());

        $this->clearStats();

        return response()->json([
            'message' => 'Application approved. Account creation is being processed.',
            'data' => new CuratedApplicationResource($application),
        ]);
    }

    public function reject(Request $request, CuratedApplication $application): JsonResponse
    {
        $this->authorize('reject', $application);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $application = $this->service->reject(
            $application,
            $request->user(),
            $request->input('reason'),
            $request->boolean('send_email', true)
        );

        $this->clearStats();

        return response()->json([
            'message' => 'Application rejected.',
            'data' => new CuratedApplicationResource($application),
        ]);
    }

    public function addNote(Request $request, CuratedApplication $application): JsonResponse
    {
        $this->authorize('addNote', $application);

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $this->service->addNote($application, $request->user(), $request->body);

        return response()->json([
            'message' => 'Note added.',
        ]);
    }

    public function bulkApprove(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'integer', 'exists:curated_applications,id'],
        ]);

        $admin = $request->user();
        $results = ['approved' => 0, 'skipped' => 0];

        $applications = CuratedApplication::whereIn('id', $request->ids)->ready()->get();

        foreach ($applications as $application) {
            $this->service->approve($application, $admin);
            $results['approved']++;
        }

        $results['skipped'] = count($request->ids) - $results['approved'];

        $this->clearStats();

        return response()->json([
            'message' => "{$results['approved']} application(s) approved.",
            'results' => $results,
        ]);
    }

    public function bulkReject(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'integer', 'exists:curated_applications,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $request->user();
        $results = ['rejected' => 0, 'skipped' => 0];

        $applications = CuratedApplication::whereIn('id', $request->ids)->ready()->get();

        foreach ($applications as $application) {
            $this->service->reject($application, $admin, $request->input('reason'));
            $results['rejected']++;
        }

        $results['skipped'] = count($request->ids) - $results['rejected'];

        $this->clearStats();

        return response()->json([
            'message' => "{$results['rejected']} application(s) rejected.",
            'results' => $results,
        ]);
    }

    protected function clearStats()
    {
        app(AdminDashboardService::class)->getReportsCount(true);
    }

    public function stats(): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        return response()->json([
            'total' => CuratedApplication::count(),
            'ready' => CuratedApplication::ready()->count(),
            'pending' => CuratedApplication::pending()->count(),
            'approved' => CuratedApplication::approved()->count(),
            'rejected' => CuratedApplication::rejected()->count(),
            'auto_rejected' => CuratedApplication::autoRejected()->count(),
            'expired' => CuratedApplication::expired()->count(),
            'avg_review_hours' => round(
                CuratedApplication::whereNotNull('reviewed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
                    ->value('avg_hours') ?? 0,
                1
            ),
        ]);
    }
}
