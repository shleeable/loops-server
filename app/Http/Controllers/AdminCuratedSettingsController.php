<?php

namespace App\Http\Controllers;

use App\Models\CuratedApplication;
use App\Models\CuratedApplicationSettings;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCuratedSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $settings = CuratedApplicationSettings::current();

        $availableAdmins = User::where('is_admin', true)->where('status', 1)->get()->map(function ($user) {
            $acct = AccountService::compact($user->profile_id, true);

            return [
                'id' => (string) $user->profile_id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => data_get($acct, 'avatar'),
                'name' => $user->name,
            ];
        });

        $adminTo = collect($settings->admin_email_send_to)->map(function ($res) {
            $acct = null;
            if (! empty($res['profile_id'])) {
                $acct = AccountService::compact($res['profile_id'], true);
            }

            return [
                'profile_id' => $res['profile_id'] ?? null,
                'email' => $res['email'],
                'account' => $acct,
            ];
        });

        return response()->json([
            'min_age' => $settings->min_age,
            'guidelines' => $settings->guidelines,
            'questions' => $settings->questions_list,
            'rejection_reasons' => $settings->rejection_reasons_list,
            'admin_email_send_to' => $adminTo,
            'auto_expire_after' => $settings->auto_expire_after,
            'auto_expire_unverified_after' => $settings->auto_expire_unverified_after,
            'approval_template' => $settings->approval_template,
            'rejection_template' => $settings->rejection_template,
            'send_rejection_email' => $settings->send_rejection_email,
            'available_admins' => $availableAdmins,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $validated = $request->validate([
            'min_age' => ['nullable', 'integer', 'min:13', 'max:25'],
            'guidelines' => ['nullable', 'string', 'max:2000'],
            'questions' => ['nullable', 'array', 'max:20'],
            'questions.*.id' => ['nullable', 'string', 'max:100'],
            'questions.*.label' => ['required_with:questions', 'string', 'max:500'],
            'questions.*.type' => ['required_with:questions', 'string', 'in:text,textarea,select'],
            'questions.*.required' => ['nullable', 'boolean'],
            'questions.*.options' => ['nullable', 'array', 'max:20'],
            'questions.*.options.*' => ['nullable', 'string', 'max:200'],
            'auto_expire_after' => ['nullable', 'integer', 'min:0', 'max:365'],
            'auto_expire_unverified_after' => ['nullable', 'integer', 'min:0', 'max:365'],
            'approval_template' => ['nullable', 'string', 'max:5000'],
            'rejection_template' => ['nullable', 'string', 'max:5000'],
            'send_rejection_email' => ['nullable', 'boolean'],
            'admin_email_send_to' => ['nullable', 'array', 'max:10'],
            'admin_email_send_to.*.profile_id' => ['nullable', 'string'],
            'admin_email_send_to.*.email' => ['required', 'email:rfc,dns,spoof,strict', 'max:255'],
        ]);

        $settings = CuratedApplicationSettings::current();

        if (isset($validated['admin_email_send_to'])) {
            $validated['admin_email_send_to'] = collect($validated['admin_email_send_to'])
                ->unique('email')
                ->map(fn ($entry) => [
                    'profile_id' => $entry['profile_id'] ?? null,
                    'email' => $entry['email'],
                ])
                ->values()
                ->all();
        }

        if (isset($validated['questions'])) {
            $validated['questions'] = CuratedApplicationSettings::normalizeQuestions($validated['questions']);
        }

        $settings->update($validated);

        return response()->json([
            'message' => 'Settings updated.',
            'data' => [
                'min_age' => $settings->min_age,
                'guidelines' => $settings->guidelines,
                'questions' => $settings->questions_list,
                'rejection_reasons' => $settings->rejection_reasons_list,
                'admin_email_send_to' => $settings->admin_email_send_to,
                'auto_expire_after' => $settings->auto_expire_after,
                'auto_expire_unverified_after' => $settings->auto_expire_unverified_after,
                'approval_template' => $settings->approval_template,
                'rejection_template' => $settings->rejection_template,
                'send_rejection_email' => $settings->send_rejection_email,
            ],
        ]);
    }

    public function addQuestion(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:500'],
            'type' => ['required', 'string', 'in:text,textarea,select'],
            'required' => ['nullable', 'boolean'],
            'options' => ['nullable', 'array', 'max:20'],
            'options.*' => ['nullable', 'string', 'max:200'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $questions = $settings->questions_list;

        $newQuestion = [
            'id' => 'q_'.now()->timestamp.'_'.count($questions),
            'label' => trim($validated['label']),
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
        ];

        if ($validated['type'] === 'select') {
            $newQuestion['options'] = array_values(
                array_filter(
                    array_map('trim', $validated['options'] ?? []),
                    fn ($opt) => $opt !== ''
                )
            );
        }

        $questions[] = $newQuestion;
        $settings->update(['questions' => $questions]);

        return response()->json([
            'message' => 'Question added.',
            'question' => $newQuestion,
            'questions' => $settings->fresh()->questions_list,
        ], 201);
    }

    public function updateQuestion(Request $request, string $questionId): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:500'],
            'type' => ['required', 'string', 'in:text,textarea,select'],
            'required' => ['nullable', 'boolean'],
            'options' => ['nullable', 'array', 'max:20'],
            'options.*' => ['nullable', 'string', 'max:200'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $questions = $settings->questions_list;
        $index = $this->findQuestionIndex($questions, $questionId);

        if ($index === null) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        $questions[$index] = [
            'id' => $questionId,
            'label' => trim($validated['label']),
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
        ];

        if ($validated['type'] === 'select') {
            $questions[$index]['options'] = array_values(
                array_filter(
                    array_map('trim', $validated['options'] ?? []),
                    fn ($opt) => $opt !== ''
                )
            );
        }

        $settings->update(['questions' => array_values($questions)]);

        return response()->json([
            'message' => 'Question updated.',
            'question' => $questions[$index],
            'questions' => $settings->fresh()->questions_list,
        ]);
    }

    public function deleteQuestion(string $questionId): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $settings = CuratedApplicationSettings::current();
        $questions = $settings->questions_list;
        $index = $this->findQuestionIndex($questions, $questionId);

        if ($index === null) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        array_splice($questions, $index, 1);
        $settings->update(['questions' => array_values($questions)]);

        return response()->json([
            'message' => 'Question removed.',
            'questions' => $settings->fresh()->questions_list,
        ]);
    }

    public function reorderQuestions(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $questions = $settings->questions_list;
        $questionMap = collect($questions)->keyBy('id');

        $reordered = [];
        foreach ($request->order as $id) {
            if ($questionMap->has($id)) {
                $reordered[] = $questionMap->get($id);
            }
        }

        $reorderedIds = array_column($reordered, 'id');
        foreach ($questions as $q) {
            if (! in_array($q['id'], $reorderedIds)) {
                $reordered[] = $q;
            }
        }

        $settings->update(['questions' => $reordered]);

        return response()->json([
            'message' => 'Questions reordered.',
            'questions' => $settings->fresh()->questions_list,
        ]);
    }

    public function addRejectionReason(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'note' => ['nullable', 'string', 'max:1000'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $reasons = $settings->rejection_reasons_list;

        $newReason = [
            'id' => 'rr_'.now()->timestamp.'_'.count($reasons),
            'title' => trim($validated['title']),
            'note' => isset($validated['note']) ? trim($validated['note']) : null,
            'reason' => trim($validated['reason']),
        ];

        $reasons[] = $newReason;
        $settings->update(['rejection_reasons' => $reasons]);

        return response()->json([
            'message' => 'Rejection reason added.',
            'reason' => $newReason,
            'rejection_reasons' => $settings->fresh()->rejection_reasons_list,
        ], 201);
    }

    public function updateRejectionReason(Request $request, string $reasonId): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'note' => ['nullable', 'string', 'max:1000'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $reasons = $settings->rejection_reasons_list;
        $index = $this->findReasonIndex($reasons, $reasonId);

        if ($index === null) {
            return response()->json(['message' => 'Rejection reason not found.'], 404);
        }

        $reasons[$index] = [
            'id' => $reasonId,
            'title' => trim($validated['title']),
            'note' => isset($validated['note']) ? trim($validated['note']) : null,
            'reason' => trim($validated['reason']),
        ];

        $settings->update(['rejection_reasons' => array_values($reasons)]);

        return response()->json([
            'message' => 'Rejection reason updated.',
            'reason' => $reasons[$index],
            'rejection_reasons' => $settings->fresh()->rejection_reasons_list,
        ]);
    }

    public function deleteRejectionReason(string $reasonId): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $settings = CuratedApplicationSettings::current();
        $reasons = $settings->rejection_reasons_list;
        $index = $this->findReasonIndex($reasons, $reasonId);

        if ($index === null) {
            return response()->json(['message' => 'Rejection reason not found.'], 404);
        }

        array_splice($reasons, $index, 1);
        $settings->update(['rejection_reasons' => array_values($reasons)]);

        return response()->json([
            'message' => 'Rejection reason removed.',
            'rejection_reasons' => $settings->fresh()->rejection_reasons_list,
        ]);
    }

    public function reorderRejectionReasons(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CuratedApplication::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string'],
        ]);

        $settings = CuratedApplicationSettings::current();
        $reasons = $settings->rejection_reasons_list;
        $reasonMap = collect($reasons)->keyBy('id');

        $reordered = [];
        foreach ($request->order as $id) {
            if ($reasonMap->has($id)) {
                $reordered[] = $reasonMap->get($id);
            }
        }

        $reorderedIds = array_column($reordered, 'id');
        foreach ($reasons as $r) {
            if (! in_array($r['id'], $reorderedIds)) {
                $reordered[] = $r;
            }
        }

        $settings->update(['rejection_reasons' => $reordered]);

        return response()->json([
            'message' => 'Rejection reasons reordered.',
            'rejection_reasons' => $settings->fresh()->rejection_reasons_list,
        ]);
    }

    private function findQuestionIndex(array $questions, string $questionId): ?int
    {
        foreach ($questions as $i => $q) {
            if (($q['id'] ?? '') === $questionId) {
                return $i;
            }
        }

        return null;
    }

    private function findReasonIndex(array $reasons, string $reasonId): ?int
    {
        foreach ($reasons as $i => $r) {
            if (($r['id'] ?? '') === $reasonId) {
                return $i;
            }
        }

        return null;
    }
}
