<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SmsOtp;
use App\Services\HotsmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorSettingsController extends Controller
{
    public function __construct(private readonly HotsmsService $sms)
    {
    }

    /**
     * Start the enable flow: send OTP to user's phone.
     */
    public function enableSend(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'لم تضف رقم هاتفك بعد. أضفه من صفحة "بياناتي" أولاً.',
            ], 422);
        }

        if ($user->sms_2fa_enabled) {
            return response()->json(['success' => false, 'message' => 'الحماية الثنائية مُفعّلة بالفعل.'], 422);
        }

        $phone = $this->sms->normalizePhone($user->phone);
        $cooldown = (int) config('hotsms.otp.cooldown_seconds', 60);

        $recent = SmsOtp::where('user_id', $user->id)
            ->where('purpose', SmsOtp::PURPOSE_PHONE_VERIFY)
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recent) {
            return response()->json(['success' => false, 'message' => "انتظر {$cooldown} ثانية قبل طلب رمز جديد."], 429);
        }

        $code = str_pad((string) random_int(0, 10 ** config('hotsms.otp.length', 6) - 1), config('hotsms.otp.length', 6), '0', STR_PAD_LEFT);

        SmsOtp::create([
            'user_id' => $user->id,
            'phone' => $phone,
            'code' => $code,
            'purpose' => SmsOtp::PURPOSE_PHONE_VERIFY,
            'expires_at' => now()->addMinutes(config('hotsms.otp.ttl_minutes', 10)),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        $text = $this->sms->buildOtpMessage($code, 'phone_verify');
        $result = $this->sms->send($phone, $text);

        if (! $result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message']], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'أُرسل رمز التحقق إلى '.$this->maskPhone($phone),
            'phone_mask' => $this->maskPhone($phone),
        ]);
    }

    /**
     * Verify OTP and enable SMS 2FA.
     */
    public function enableVerify(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'code' => ['required', 'string', 'size:'.config('hotsms.otp.length', 6)],
        ]);

        $phone = $this->sms->normalizePhone($user->phone);

        $otp = SmsOtp::where('user_id', $user->id)
            ->where('phone', $phone)
            ->where('purpose', SmsOtp::PURPOSE_PHONE_VERIFY)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages(['code' => 'الرمز غير صالح أو انتهت صلاحيته.']);
        }

        if (! hash_equals((string) $otp->code, (string) $data['code'])) {
            $otp->increment('attempts');
            throw ValidationException::withMessages(['code' => 'رمز غير صحيح.']);
        }

        $otp->update(['used_at' => now()]);

        $user->update([
            'sms_2fa_enabled' => true,
            'sms_2fa_enabled_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_MFA_ENABLED,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['method' => 'sms'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل الحماية الثنائية عبر SMS.',
        ]);
    }

    /**
     * Disable SMS 2FA — requires current password.
     */
    public function disable(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'كلمة المرور غير صحيحة.']);
        }

        $user->update([
            'sms_2fa_enabled' => false,
            'sms_2fa_enabled_at' => null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_MFA_DISABLED,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['method' => 'sms'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إيقاف الحماية الثنائية.',
        ]);
    }

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) {
            return '****';
        }

        return substr($phone, 0, 3).str_repeat('*', strlen($phone) - 5).substr($phone, -2);
    }
}
