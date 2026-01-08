<?php

namespace App\Services;

use App\Mail\NewVersionAvailable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VersionCheckService
{
    protected const BEACON_URL = 'https://beacon.joinloops.org/latest.json';

    protected const CACHE_KEY = 'version_check_result';

    protected const FAILURE_KEY = 'version_check_failures';

    protected const NOTIFICATION_FILE = 'version_notifications.json';

    protected const CACHE_TTL = 21600;

    protected const FAILURE_THRESHOLD_DAYS = 3;

    protected const REQUEST_TIMEOUT = 10;

    /**
     * Check for updates and return structured response
     */
    public function checkForUpdates(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->get(self::BEACON_URL);

            if ($response->successful()) {
                $data = $response->json();
                $result = $this->buildResult($data);

                Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL);

                Cache::forget(self::FAILURE_KEY);

                if ($result['update_available']) {
                    $this->handleNewVersionNotification($result);
                }

                return $result;
            }

            return $this->handleFailure("HTTP {$response->status()}");

        } catch (\Exception $e) {
            Log::warning('Version check failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->handleFailure($e->getMessage());
        }
    }

    /**
     * Build result comparing versions
     */
    protected function buildResult(array $beaconData): array
    {
        $currentVersion = $this->normalizeVersion(app('app_version'));
        $latestVersion = $this->normalizeVersion($beaconData['version']);

        $updateAvailable = version_compare($latestVersion, $currentVersion, '>');

        return [
            'status' => 'success',
            'checked_at' => now()->toIso8601String(),
            'current_version' => app('app_version'),
            'latest_version' => $beaconData['version'],
            'update_available' => $updateAvailable,
            'release' => $updateAvailable ? [
                'name' => $beaconData['name'],
                'url' => $beaconData['url'],
                'published_at' => $beaconData['published_at'],
            ] : null,
        ];
    }

    /**
     * Handle check failures with tracking
     */
    protected function handleFailure(string $reason): array
    {
        $failures = Cache::get(self::FAILURE_KEY, []);

        $failures[] = now()->toIso8601String();

        $failures = collect($failures)
            ->filter(fn ($timestamp) => Carbon::parse($timestamp)->isAfter(now()->subDays(7))
            )
            ->values()
            ->toArray();

        Cache::put(self::FAILURE_KEY, $failures, 86400 * 7);

        $firstFailure = Carbon::parse($failures[0]);
        $daysFailing = $firstFailure->diffInDays(now());
        $prolongedFailure = $daysFailing >= self::FAILURE_THRESHOLD_DAYS;

        return [
            'status' => 'failure',
            'checked_at' => now()->toIso8601String(),
            'current_version' => app('app_version'),
            'error' => $prolongedFailure ? 'prolonged_failure' : 'temporary_failure',
            'failure_details' => [
                'reason' => $reason,
                'failure_count' => count($failures),
                'first_failed_at' => $failures[0],
                'days_failing' => $daysFailing,
                'prolonged' => $prolongedFailure,
            ],
        ];
    }

    /**
     * Handle new version notification logic
     */
    protected function handleNewVersionNotification(array $versionData): void
    {
        if (config('app.env') === 'production' && config('mail.default') === 'log') {
            return;
        }

        $notificationData = $this->getNotificationData();
        $latestVersion = $versionData['latest_version'];

        if (
            isset($notificationData['last_notified_version']) &&
            $notificationData['last_notified_version'] === $latestVersion
        ) {
            return;
        }

        $admin = User::where('is_admin', true)->first();

        if (! $admin) {
            Log::warning('No admin user found for version notification');

            return;
        }

        try {
            Mail::to($admin->email)->send(new NewVersionAvailable($versionData));

            $this->updateNotificationData([
                'last_notified_version' => $latestVersion,
                'last_notified_at' => now()->toIso8601String(),
                'notified_email' => $admin->email,
            ]);

            Log::info('Version update notification sent', [
                'version' => $latestVersion,
                'recipient' => $admin->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send version notification email', [
                'error' => $e->getMessage(),
                'version' => $latestVersion,
            ]);
        }
    }

    /**
     * Get notification tracking data from JSON file
     */
    protected function getNotificationData(): array
    {
        $filePath = storage_path('app/private/'.self::NOTIFICATION_FILE);

        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);

        return json_decode($content, true) ?? [];
    }

    /**
     * Update notification tracking data
     */
    protected function updateNotificationData(array $data): void
    {
        $filePath = storage_path('app/private/'.self::NOTIFICATION_FILE);

        $directory = dirname($filePath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $existing = $this->getNotificationData();
        $merged = array_merge($existing, $data);

        File::put($filePath, json_encode($merged, JSON_PRETTY_PRINT));
    }

    /**
     * Normalize version strings for comparison
     */
    protected function normalizeVersion(string $version): string
    {
        return ltrim($version, 'v');
    }

    /**
     * Force a fresh check (bypass cache)
     */
    public function forceCheck(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->checkForUpdates();
    }

    /**
     * Get current failure status without checking
     */
    public function getFailureStatus(): ?array
    {
        $failures = Cache::get(self::FAILURE_KEY);

        if (! $failures) {
            return null;
        }

        $firstFailure = Carbon::parse($failures[0]);
        $daysFailing = $firstFailure->diffInDays(now());

        return [
            'failure_count' => count($failures),
            'first_failed_at' => $failures[0],
            'days_failing' => $daysFailing,
            'prolonged' => $daysFailing >= self::FAILURE_THRESHOLD_DAYS,
        ];
    }

    /**
     * Get notification history
     */
    public function getNotificationHistory(): array
    {
        return $this->getNotificationData();
    }
}
