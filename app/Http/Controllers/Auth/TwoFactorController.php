<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SmsOtp;
use App\Models\SsoSession;
use App\Models\User;
use App\Services\HotsmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(private readonly HotsmsService $sms)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('2fa_pending');

        if (! $pending || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            return redirect()->route('login')->withErrors(['email' => 'انتهت صلاحية الجلسة. سجّل دخولك من جديد.']);
        }

        return view('auth.two-factor', [
            'phone_mask' => $this->maskPhone($pending['phone'] ?? ''),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $pending = $request->session()->get('2fa_pending');

        if (! $pending || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة. سجّل دخولك من جديد.', 'redirect' => route('login')], 422);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:'.config('hotsms.otp.length', 6)],
        ]);

        $otp = SmsOtp::where('phone', $pending['phone'])
            ->where('purpose', SmsOtp::PURPOSE_LOGIN_2FA)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $maxAttempts = (int) config('hotsms.otp.max_attempts', 5);

        if (! $otp || $otp->attempts >= $maxAttempts) {
            throw ValidationException::withMessages(['code' => 'الرمز غير صالح. اطلب رمزاً جديداً.']);
        }

        if (! hash_equals((string) $otp->code, (string) $data['code'])) {
            $otp->increment('attempts');
            throw ValidationException::withMessages(['code' => 'رمز غير صحيح. المحاولات المتبقية: '.($maxAttempts - $otp->attempts)]);
        }

        $otp->update(['used_at' => now()]);

        $user = User::findOrFail($pending['user_id']);
        Auth::login($user, $pending['remember'] ?? false);
        $request->session()->regenerate();
        $request->session()->forget('2fa_pending');

        $ssoSession = SsoSession::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'mfa_verified' => true,
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
                'mfa' => 'sms',
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'redirect' => session()->pull('url.intended', route('dashboard')),
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $pending = $request->session()->get('2fa_pending');

        if (! $pending) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة.'], 422);
        }

        $cooldown = (int) config('hotsms.otp.cooldown_seconds', 60);
        $recent = SmsOtp::where('phone', $pending['phone'])
            ->where('purpose', SmsOtp::PURPOSE_LOGIN_2FA)
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recent) {
            return response()->json(['success' => false, 'message' => "انتظر {$cooldown} ثانية قبل طلب رمز جديد."], 429);
        }

        static::sendOtp($pending['user_id'], $pending['phone'], $request, $this->sms);

        $request->session()->put('2fa_pending.expires_at', now()->addMinutes(15)->timestamp);

        return response()->json(['success' => true, 'message' => 'تم إرسال رمز جديد.']);
    }

    /**
     * Generate + send OTP for login 2FA. Used by LoginController.
     */
    public static function sendOtp(string $userId, string $phone, Request $request, HotsmsService $sms): void
    {
        $code = str_pad((string) random_int(0, 10 ** config('hotsms.otp.length', 6) - 1), config('hotsms.otp.length', 6), '0', STR_PAD_LEFT);

        SmsOtp::create([
            'user_id' => $userId,
            'phone' => $phone,
            'code' => $code,
            'purpose' => SmsOtp::PURPOSE_LOGIN_2FA,
            'expires_at' => now()->addMinutes(config('hotsms.otp.ttl_minutes', 10)),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        $text = $sms->buildOtpMessage($code, 'login_2fa');
        $sms->send($phone, $text);
    }

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) {
            return '****';
        }

        return substr($phone, 0, 3).str_repeat('*', strlen($phone) - 5).substr($phone, -2);
    }
}
