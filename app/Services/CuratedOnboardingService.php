<?php

namespace App\Services;

use App\Jobs\ProcessCuratedApproval;
use App\Mail\CuratedApplicationReceivedMail;
use App\Mail\CuratedApplicationRejectedMail;
use App\Mail\NewCuratedApplicationMail;
use App\Models\CuratedApplication;
use App\Models\CuratedApplicationOnboarding;
use App\Models\CuratedApplicationSettings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CuratedOnboardingService
{
    public function submit(array $data, ?string $ipAddress = null): CuratedApplication
    {
        $settings = CuratedApplicationSettings::current();
        $birthdate = Carbon::parse($data['birthdate']);
        $minAge = $settings->min_age;

        $application = DB::transaction(function () use ($data, $birthdate, $minAge, $ipAddress) {
            $isUnderage = $minAge && $birthdate->age < $minAge;

            $application = CuratedApplication::create([
                'email' => $data['email'],
                'username_requested' => $data['username_requested'] ?? null,
                'reason' => $data['reason'],
                'fediverse_account' => $data['fediverse_account'] ?? null,
                'custom_answers' => $data['custom_answers'] ?? null,
                'status' => $isUnderage
                    ? CuratedApplication::STATUS_AUTO_REJECTED
                    : CuratedApplication::STATUS_PENDING,
                'status_reason' => $isUnderage
                    ? "Auto-rejected: applicant is under the minimum age requirement of {$minAge}"
                    : null,
                'ip_hash' => $ipAddress ? hash('sha256', $ipAddress) : null,
                'email_verification_token' => Str::random(64),
            ]);

            $application->birthdate = $birthdate;
            $application->save();

            return $application;
        });

        if ($application->status === CuratedApplication::STATUS_PENDING) {
            Mail::to($application->email)->send(
                new CuratedApplicationReceivedMail($application)
            );
        }

        return $application;
    }

    public function approve(CuratedApplication $application, User $admin): CuratedApplication
    {
        $this->ensureReady($application);

        $application->update([
            'status' => CuratedApplication::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ProcessCuratedApproval::dispatch($application);

        return $application->fresh();
    }

    public function reject(
        CuratedApplication $application,
        User $admin,
        ?string $reason = null,
        bool $sendEmail = true
    ): CuratedApplication {
        $this->ensureReadyOrPending($application);

        $settings = CuratedApplicationSettings::current();

        $application->update([
            'status' => CuratedApplication::STATUS_REJECTED,
            'status_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        if ($sendEmail && $settings->send_rejection_email) {
            Mail::to($application->email)->send(
                new CuratedApplicationRejectedMail($application, $reason, $settings->rejection_template)
            );
        }

        return $application->fresh();
    }

    public function createAccountForApproval(CuratedApplication $application): CuratedApplicationOnboarding
    {
        if (! $application->isApproved()) {
            throw new \RuntimeException('Cannot create account for non-approved application.');
        }

        $magicKey = 'co1'.Str::random(32);
        $user = DB::transaction(function () use ($application, $magicKey) {
            $user = CuratedApplicationOnboarding::create([
                'application_id' => $application->id,
                'email' => $application->email,
                'username_requested' => $application->username_requested ?? null,
                'magic_key' => $magicKey,
                'expires_after' => now()->addDays(14),
            ]);

            return $user;
        });

        return $user;
    }

    public function expireStale(): int
    {
        $days = CuratedApplicationSettings::current()->auto_expire_after;

        if (! $days) {
            return 0;
        }

        return CuratedApplication::stale($days)->delete();
    }

    public function expireStaleEmailVerifications(): int
    {
        $days = CuratedApplicationSettings::current()->auto_expire_unverified_after;

        if (! $days) {
            return 0;
        }

        $applicationIds = CuratedApplicationOnboarding::stale($days)->pluck('application_id');

        return CuratedApplication::whereIn('id', $applicationIds)->delete();
    }

    public function verifyEmail(string $token): ?CuratedApplication
    {
        $application = CuratedApplication::where('email_verification_token', $token)->where('status', 'pending')->first();

        if (! $application) {
            return null;
        }

        $application->markEmailVerified();

        if ($application->isReady()) {
            $this->notifyAdmins($application);
        }

        return $application;
    }

    public function addNote(CuratedApplication $application, User $admin, string $body): void
    {
        $application->notes()->create([
            'admin_id' => $admin->id,
            'body' => $body,
        ]);
    }

    public function isEnabled(): bool
    {
        return app(ConfigService::class)->registrationMode() === 'curated';
    }

    public function getPublicConfig(): array
    {
        $settings = CuratedApplicationSettings::current();

        return [
            'registration_mode' => config('loops.registration_mode', 'open'),
            ...$settings->toPublicArray(),
        ];
    }

    public function getSettings(): CuratedApplicationSettings
    {
        return CuratedApplicationSettings::current();
    }

    private function ensureReady(CuratedApplication $application): void
    {
        if (! $application->isReady()) {
            throw ValidationException::withMessages([
                'status' => "This application is already {$application->status} and cannot be modified.",
            ]);
        }
    }

    private function ensureReadyOrPending(CuratedApplication $application): void
    {
        if (! $application->isReady() && ! $application->isPending()) {
            throw ValidationException::withMessages([
                'status' => "This application is already {$application->status} and cannot be modified.",
            ]);
        }
    }

    private function notifyAdmins(CuratedApplication $application): void
    {
        $settings = CuratedApplicationSettings::current();
        $admins = $settings->notify_emails;
        foreach ($admins as $email) {
            Mail::to($email)->send(new NewCuratedApplicationMail($application));
        }
    }
}
