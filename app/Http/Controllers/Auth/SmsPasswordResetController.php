<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SmsOtp;
use App\Models\User;
use App\Services\HotsmsService;
use App\Services\PasswordHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SmsPasswordResetController extends Controller
{
    public function __construct(private readonly HotsmsService $sms)
    {
    }

    public function showPhoneForm(): View
    {
        return view('auth.sms-reset.phone');
    }

    public function sendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
        ]);

        $normalized = $this->sms->normalizePhone($data['phone']);

        $user = User::where('phone', $normalized)
            ->orWhere('phone', '+'.$normalized)
            ->orWhere('phone', 'like', '%'.substr($normalized, -9))
            ->first();

        $cooldown = (int) config('hotsms.otp.cooldown_seconds', 60);
        $recent = SmsOtp::where('phone', $normalized)
            ->where('purpose', SmsOtp::PURPOSE_PASSWORD_RESET)
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recent) {
            return response()->json([
                'success' => false,
                'message' => "انتظر {$cooldown} ثانية قبل طلب رمز جديد.",
            ], 429);
        }

        $code = str_pad((string) random_int(0, 10 ** config('hotsms.otp.length', 6) - 1), config('hotsms.otp.length', 6), '0', STR_PAD_LEFT);

        SmsOtp::create([
            'user_id' => $user?->id,
            'phone' => $normalized,
            'code' => $code,
            'purpose' => SmsOtp::PURPOSE_PASSWORD_RESET,
            'expires_at' => now()->addMinutes(config('hotsms.otp.ttl_minutes', 10)),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        if ($user) {
            $text = $this->sms->buildOtpMessage($code, 'password_reset');
            $result = $this->sms->send($normalized, $text);

            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_PASSWORD_RESET_REQUESTED,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'via' => 'sms',
                    'phone' => $normalized,
                    'sms_result' => $result['code'] ?? 'unknown',
                ],
            ]);

            if (! $result['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'إن كان الرقم مسجّلاً لدينا، فسيصلك رمز التحقق خلال لحظات.',
            'phone' => $normalized,
            'redirect' => route('password.sms.verify.show', ['phone' => $normalized]),
        ]);
    }

    public function showVerifyForm(Request $request): View
    {
        return view('auth.sms-reset.verify', [
            'phone' => $request->query('phone', ''),
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:'.config('hotsms.otp.length', 6)],
        ], [
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون '.config('hotsms.otp.length', 6).' أرقام',
        ]);

        $phone = $this->sms->normalizePhone($data['phone']);

        $otp = SmsOtp::where('phone', $phone)
            ->where('purpose', SmsOtp::PURPOSE_PASSWORD_RESET)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => 'الرمز غير صالح أو انتهت صلاحيته. اطلب رمزاً جديداً.',
            ]);
        }

        $maxAttempts = (int) config('hotsms.otp.max_attempts', 5);
        if ($otp->attempts >= $maxAttempts) {
            $otp->update(['used_at' => now()]);
            throw ValidationException::withMessages([
                'code' => 'تم تجاوز عدد المحاولات المسموح. اطلب رمزاً جديداً.',
            ]);
        }

        if (! hash_equals((string) $otp->code, (string) $data['code'])) {
            $otp->increment('attempts');
            throw ValidationException::withMessages([
                'code' => 'رمز غير صحيح. محاولات متبقية: '.($maxAttempts - $otp->attempts),
            ]);
        }

        $token = hash('sha256', $otp->id.':'.$otp->code.':'.config('app.key'));
        $request->session()->put('sms_reset_token', [
            'otp_id' => $otp->id,
            'phone' => $phone,
            'token' => $token,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق. اختر كلمة مرور جديدة.',
            'redirect' => route('password.sms.reset.show'),
        ]);
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        $session = $request->session()->get('sms_reset_token');

        if (! $session || ($session['expires_at'] ?? 0) < now()->timestamp) {
            return redirect()->route('password.sms.phone')
                ->withErrors(['phone' => 'انتهت صلاحية الجلسة. ابدأ من جديد.']);
        }

        return view('auth.sms-reset.reset', [
            'phone' => $session['phone'],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $session = $request->session()->get('sms_reset_token');

        if (! $session || ($session['expires_at'] ?? 0) < now()->timestamp) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة.'], 422);
        }

        $otp = SmsOtp::find($session['otp_id']);
        if (! $otp || $otp->isUsed() || $otp->isExpired()) {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صالح.'], 422);
        }

        $user = User::where('phone', $session['phone'])
            ->orWhere('phone', 'like', '%'.substr($session['phone'], -9))
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 422);
        }

        $history = app(PasswordHistoryService::class);

        if ($history->matchesRecent($user, $data['password'])) {
            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_PASSWORD_REUSE_BLOCKED,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['source' => 'sms_reset'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'لا يمكن استخدام إحدى آخر '.PasswordHistoryService::HISTORY_SIZE.' كلمات مرور سابقة.',
                'errors' => ['password' => ['لا يمكن استخدام إحدى آخر '.PasswordHistoryService::HISTORY_SIZE.' كلمات مرور سابقة.']],
            ], 422);
        }

        $previousHash = $user->password;

        $user->update(['password' => Hash::make($data['password'])]);
        $history->record($user, $previousHash);
        $otp->update(['used_at' => now()]);
        $request->session()->forget('sms_reset_token');

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_PASSWORD_RESET_COMPLETED,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['via' => 'sms'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح.',
            'redirect' => route('login'),
        ]);
    }
}
