<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Requests\UpdateDataSettingsRequest;
use App\Jobs\User\ProcessDataExport;
use App\Models\DataExport;
use App\Services\UserAuditLogService;
use App\Services\UserDataService;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountDataController extends Controller
{
    use ApiHelpers;

    public function __construct(
        private UserDataService $userDataService,
        protected UserAuditLogService $auditService
    ) {
        $this->middleware('auth');
        $this->auditService = $auditService;
    }

    public function getDataInsights(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = Cache::remember(
            "acct-data:data-insights:{$user->id}",
            now()->addHours(12),
            function () use ($user) {

                return [
                    'videos' => $user->videos()->count(),
                    'comments' => $user->comments()->count(),
                    'likes' => $user->likes()->count(),
                    'watchTime' => $user->getTotalWatchTimeInHours(),
                    'totalSize' => $user->getTotalDataSize(),
                ];
            });

        return $this->data($stats);
    }

    public function getDataSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->getOrCreateDataSettings();

        $res = Cache::remember(
            "acct-data:data-settings:{$user->id}",
            now()->addHours(12),
            function () use ($settings) {
                return [
                    'dataRetentionPeriod' => $settings->data_retention_period,
                    'analyticsTracking' => $settings->analytics_tracking,
                    'researchDataSharing' => $settings->research_data_sharing,
                ];
            });

        return $this->data($res);
    }

    public function updateDataSettings(UpdateDataSettingsRequest $request): JsonResponse
    {
        $user = $request->user();

        $originalData = $user->dataSettings->only(['data_retention_period', 'analytics_tracking', 'research_data_sharing']);

        Cache::forget("acct-data:data-settings:{$user->id}");
        $settings = $user->getOrCreateDataSettings();

        $settings->update([
            'data_retention_period' => $request->data_retention_period,
            'analytics_tracking' => $request->analytics_tracking,
            'research_data_sharing' => $request->research_data_sharing,
        ]);

        $changedFields = [];
        foreach ($request->validated() as $key => $value) {
            if ($originalData[$key] !== $value) {
                $changedFields[] = [$key => $value];
            }
        }

        $this->auditService->logAccountDataSettingsUpdated($user, $changedFields);

        return $this->data(['message' => 'Settings updated successfully']);
    }

    public function requestFullExport(Request $request): JsonResponse
    {
        $user = $request->user();

        $existingExport = $user->dataExports()
            ->whereIn('status', ['pending', 'processing'])
            ->where('type', 'complete')
            ->first();

        if ($existingExport) {
            return $this->data([
                'message' => 'An export is already in progress',
                'export' => $existingExport,
            ], 409);
        }

        $export = $user->dataExports()->create([
            'type' => 'complete',
            'status' => 'pending',
        ]);

        ProcessDataExport::dispatch($export);

        return $this->data([
            'message' => 'Export request submitted successfully',
            'export' => $export,
        ]);
    }

    public function requestSelectiveExport(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(['videos', 'profile', 'interactions', 'followers'])],
        ]);

        $user = $request->user();
        $type = $request->type;

        Cache::forget("acct-data:data-export-history:{$user->id}");

        $existingExport = $user->dataExports()
            ->whereIn('status', ['pending', 'processing'])
            ->where('type', $type)
            ->first();

        if ($existingExport) {
            return $this->error('An export of this type is already in progress');
        }

        $recentExport = DataExport::whereUserId($user->id)
            ->whereIn('status', ['pending', 'processing', 'ready', 'failed'])
            ->where('type', $type)
            ->where('created_at', '>', now()->subHours(12))
            ->exists();

        if ($recentExport) {
            return $this->error('You have to wait 12 hours between export requests');
        }

        $export = $user->dataExports()->create([
            'type' => $type,
            'status' => 'pending',
        ]);

        ProcessDataExport::dispatch($export);

        return response()->json([
            'message' => 'Export request submitted successfully',
            'export' => $export,
        ]);
    }

    public function getExportHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $exports = Cache::remember(
            "acct-data:data-export-history:{$user->id}",
            now()->addHours(12),
            function () use ($user) {
                return DataExport::whereUserId($user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(30)
                    ->get()
                    ->map(function ($export) {
                        return [
                            'id' => $export->id,
                            'type' => $this->getExportTypeName($export->type),
                            'date' => $export->created_at->format('c'),
                            'status' => ucfirst($export->status),
                            'size' => $export->formatted_size,
                            'downloadUrl' => $export->download_url,
                        ];
                    });
            });

        return $this->data($exports);
    }

    public function downloadExport(Request $request, $id)
    {
        $user = $request->user();

        $export = DataExport::whereUserId($user->id)->findOrFail($id);

        if ($export->status !== 'ready' || $export->isExpired()) {
            return $this->error('Export file not available', 404);
        }

        if (! Storage::disk('exports')->exists($export->file_path)) {
            return $this->error('Export file not found', 404);
        }

        return Storage::disk('exports')->download(
            $export->file_path,
            "loops-data-export-{$export->type}-{$export->created_at->format('Y-m-d')}.zip"
        );
    }

    public function deleteData(Request $request): JsonResponse
    {
        // todo: finish impl
        return abort(404);
    }

    public function cleanupExpiredExports(): JsonResponse
    {
        $expiredExports = DataExport::where('expires_at', '<', now())
            ->where('status', 'ready')
            ->get();

        foreach ($expiredExports as $export) {
            if ($export->file_path && Storage::disk('exports')->exists($export->file_path)) {
                Storage::disk('exports')->delete($export->file_path);
            }
            $export->markAsExpired();
        }

        return response()->json([
            'message' => "Cleaned up {$expiredExports->count()} expired exports",
        ]);
    }

    private function getExportTypeName(string $type): string
    {
        return match ($type) {
            'complete' => 'Complete account export',
            'videos' => 'Videos only',
            'profile' => 'Profile data',
            'interactions' => 'Interactions',
            'followers' => 'Social connections',
            default => ucfirst($type)
        };
    }
}
