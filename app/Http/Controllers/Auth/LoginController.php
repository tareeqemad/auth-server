<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SsoSession;
use App\Models\User;
use App\Services\AccountLockoutService;
use App\Services\HotsmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request, AccountLockoutService $lockout): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $existingUser = User::where('email', $credentials['email'])->first();

        if ($existingUser && $lockout->isLocked($existingUser)) {
            AuditLog::create([
                'user_id' => $existingUser->id,
                'event_type' => AuditLog::EVENT_LOGIN_FAILED,
                'email' => $existingUser->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'reason' => 'account_locked',
                    'locked_by_admin' => $existingUser->locked_by_admin_id !== null,
                ],
            ]);

            return $this->failure(
                $request,
                ['email' => $this->lockedMessage($existingUser, $lockout)],
            );
        }

        if (! Auth::attempt($credentials, $remember)) {
            if ($existingUser) {
                $lockout->recordFailedAttempt($existingUser, $request);
            }

            AuditLog::create([
                'user_id' => $existingUser?->id,
                'event_type' => AuditLog::EVENT_LOGIN_FAILED,
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'reason' => 'invalid_credentials',
                    'attempts' => $existingUser?->failed_login_attempts,
                ],
            ]);

            if ($existingUser && $lockout->isLocked($existingUser->refresh())) {
                return $this->failure(
                    $request,
                    ['email' => $this->lockedMessage($existingUser, $lockout)],
                );
            }

            return $this->failure(
                $request,
                ['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            );
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_LOGIN_FAILED,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['reason' => 'account_inactive'],
            ]);

            return $this->failure(
                $request,
                ['email' => 'الحساب معطّل. تواصل مع الإدارة.'],
            );
        }

        if ($user->sms_2fa_enabled && ! empty($user->phone)) {
            Auth::logout();
            $sms = app(HotsmsService::class);
            $phone = $sms->normalizePhone($user->phone);

            \App\Http\Controllers\Auth\TwoFactorController::sendOtp($user->id, $phone, $request, $sms);

            $request->session()->put('2fa_pending', [
                'user_id' => $user->id,
                'phone' => $phone,
                'remember' => $remember,
                'expires_at' => now()->addMinutes(15)->timestamp,
            ]);

            $redirect = route('login.2fa.show');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'أُرسل رمز التحقق إلى هاتفك.',
                    'redirect' => $redirect,
                    'two_factor' => true,
                ]);
            }

            return redirect($redirect);
        }

        $lockout->recordSuccessfulLogin($user);

        $request->session()->regenerate();

        $ssoSession = SsoSession::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'mfa_verified' => false,
            'last_activity_at' => now(),
            'expires_at' => now()->addHours(8),
        ]);

        $request->session()->put('sso_session_id', $ssoSession->id);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_LOGIN_SUCCESS,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'sso_session_id' => $ssoSession->id,
                'remember' => $remember,
            ],
        ]);

        $defaultRoute = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');
        $target = redirect()->intended($defaultRoute)->getTargetUrl();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $target,
            ]);
        }

        return redirect($target);
    }

    private function failure(Request $request, array $errors): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => reset($errors),
                'errors' => array_map(fn ($m) => [$m], $errors),
            ], 422);
        }

        return back()->withErrors($errors)->onlyInput('email');
    }

    private function lockedMessage(User $user, AccountLockoutService $lockout): string
    {
        if ($user->locked_by_admin_id !== null) {
            return 'تم حظر حسابك من قبل المشرف. الرجاء التواصل مع الإدارة.';
        }

        $seconds = $lockout->secondsRemaining($user);
        $minutes = (int) ceil($seconds / 60);

        return sprintf(
            'تم إيقاف حسابك مؤقتاً بعد عدة محاولات فاشلة. حاول مجدداً بعد %d دقيقة.',
            max(1, $minutes),
        );
    }
}
