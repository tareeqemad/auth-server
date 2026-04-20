<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AccountLockoutService
{
    public const MAX_FAILED_ATTEMPTS = 5;

    public const LOCKOUT_MINUTES = 15;

    public function isLocked(User $user): bool
    {
        if ($user->locked_until === null) {
            return false;
        }

        if ($user->locked_until->isFuture()) {
            return true;
        }

        $this->clearExpiredAutoLock($user);

        return false;
    }

    public function secondsRemaining(User $user): int
    {
        if (! $this->isLocked($user)) {
            return 0;
        }

        return max(0, now()->diffInSeconds($user->locked_until, false));
    }

    public function recordFailedAttempt(User $user, Request $request): void
    {
        $user->failed_login_attempts = $user->failed_login_attempts + 1;
        $user->last_failed_login_at = now();

        $shouldAutoLock = $user->failed_login_attempts >= self::MAX_FAILED_ATTEMPTS
            && $user->locked_by_admin_id === null;

        if ($shouldAutoLock) {
            $user->locked_until = now()->addMinutes(self::LOCKOUT_MINUTES);
            $user->locked_reason = 'تجاوز الحد المسموح لمحاولات الدخول الفاشلة';
            $user->locked_by_admin_id = null;
        }

        $user->save();

        if ($shouldAutoLock) {
            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_ACCOUNT_LOCKED,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'reason' => 'max_failed_attempts',
                    'locked_until' => $user->locked_until->toIso8601String(),
                    'attempts' => $user->failed_login_attempts,
                ],
            ]);
        }
    }

    public function recordSuccessfulLogin(User $user): void
    {
        if ($user->failed_login_attempts === 0 && $user->locked_until === null) {
            return;
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'locked_reason' => null,
            'locked_by_admin_id' => null,
            'last_failed_login_at' => null,
        ])->save();
    }

    public function lockByAdmin(User $user, User $admin, ?int $minutes, ?string $reason, Request $request): void
    {
        $lockedUntil = $minutes === null
            ? now()->addYears(10)
            : now()->addMinutes($minutes);

        $user->forceFill([
            'locked_until' => $lockedUntil,
            'locked_reason' => $reason ?: 'تم الحظر بواسطة المشرف',
            'locked_by_admin_id' => $admin->id,
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_ACCOUNT_LOCKED_BY_ADMIN,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'minutes' => $minutes,
                'locked_until' => $lockedUntil->toIso8601String(),
                'reason' => $reason,
            ],
        ]);
    }

    public function unlockByAdmin(User $user, User $admin, Request $request): void
    {
        $previous = [
            'locked_until' => optional($user->locked_until)->toIso8601String(),
            'locked_reason' => $user->locked_reason,
            'locked_by_admin' => $user->locked_by_admin_id !== null,
            'failed_attempts' => $user->failed_login_attempts,
        ];

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'locked_reason' => null,
            'locked_by_admin_id' => null,
            'last_failed_login_at' => null,
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_ACCOUNT_UNLOCKED_BY_ADMIN,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'previous' => $previous,
            ],
        ]);
    }

    private function clearExpiredAutoLock(User $user): void
    {
        if ($user->locked_by_admin_id !== null) {
            return;
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'locked_reason' => null,
            'last_failed_login_at' => null,
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_ACCOUNT_UNLOCKED,
            'email' => $user->email,
            'metadata' => ['reason' => 'auto_expired'],
        ]);
    }
}
