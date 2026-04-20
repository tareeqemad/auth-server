<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HotsmsService
{
    public const CODE_SUCCESS = '1001';
    public const CODE_NO_BALANCE = '1000';
    public const CODE_AUTH_ERROR = '2000';
    public const CODE_TYPE_ERROR = '3000';
    public const CODE_MISSING_FIELD = '4000';
    public const CODE_UNSUPPORTED_NUMBER = '5000';
    public const CODE_SENDER_NOT_REGISTERED = '6000';
    public const CODE_IP_NOT_AUTHORIZED = '10000';
    public const CODE_API_DISABLED = '15000';

    /**
     * Send an SMS message.
     *
     * @return array{ok: bool, code: string, message: string, message_id: ?string, raw: string}
     */
    public function send(string $phone, string $text, ?int $type = null): array
    {
        if (! $this->setting('hotsms_enabled', config('hotsms.enabled'))) {
            return $this->disabledResponse();
        }

        $phone = $this->normalizePhone($phone);
        $type ??= (int) config('hotsms.default_type', 2);

        $params = [
            'sender' => $this->setting('hotsms_sender', config('hotsms.sender')),
            'mobile' => $phone,
            'type' => $type,
            'text' => $text,
            'msg_id' => 'YES',
        ];

        $params = $this->withCredentials($params);

        try {
            $response = Http::timeout(config('hotsms.timeout', 10))
                ->asForm()
                ->get(config('hotsms.api_url').'/sendbulksms.php', $params);

            $raw = trim($response->body());
            $result = $this->parseResponse($raw);

            if (! $result['ok']) {
                Log::warning('Hotsms send failed', ['phone' => $phone, 'code' => $result['code'], 'raw' => $raw]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Hotsms send exception', ['phone' => $phone, 'error' => $e->getMessage()]);

            return [
                'ok' => false,
                'code' => 'exception',
                'message' => 'فشل الاتصال بمزوّد الـ SMS. حاول مرة أخرى لاحقاً.',
                'message_id' => null,
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check account balance.
     *
     * @return array{ok: bool, balance: ?int, raw: string, error: ?string}
     */
    public function balance(): array
    {
        if (! $this->setting('hotsms_enabled', config('hotsms.enabled'))) {
            return ['ok' => false, 'balance' => null, 'raw' => '', 'error' => 'SMS disabled'];
        }

        try {
            $response = Http::timeout(config('hotsms.timeout', 10))
                ->get(config('hotsms.api_url').'/getbalance.php', $this->withCredentials());

            $raw = trim($response->body());

            if (is_numeric($raw)) {
                return ['ok' => true, 'balance' => (int) $raw, 'raw' => $raw, 'error' => null];
            }

            return [
                'ok' => false,
                'balance' => null,
                'raw' => $raw,
                'error' => $this->errorMessageForCode($raw),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'balance' => null, 'raw' => '', 'error' => $e->getMessage()];
        }
    }

    /**
     * Build OTP message text (Arabic).
     */
    public function buildOtpMessage(string $code, ?string $purpose = null): string
    {
        $appName = config('app.name', 'GEDCO SSO');

        return match ($purpose) {
            'password_reset' => "{$appName}: رمز إعادة تعيين كلمة المرور هو {$code}. صالح لـ 10 دقائق. لا تشاركه مع أحد.",
            'login_2fa' => "{$appName}: رمز التحقق لتسجيل الدخول هو {$code}. صالح لـ 10 دقائق.",
            'phone_verify' => "{$appName}: رمز تأكيد رقم الهاتف هو {$code}.",
            default => "{$appName}: رمز التحقق هو {$code}. صالح لـ 10 دقائق.",
        };
    }

    /**
     * Normalize phone to Hotsms format: country code + local (no +, no spaces).
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        }

        $cc = config('hotsms.default_country_code', '970');

        if (str_starts_with($phone, '0') && ! str_starts_with($phone, $cc)) {
            $phone = $cc.substr($phone, 1);
        }

        if (! str_starts_with($phone, $cc) && strlen($phone) <= 10) {
            $phone = $cc.$phone;
        }

        return $phone;
    }

    private function withCredentials(array $params = []): array
    {
        $token = $this->setting('hotsms_api_token', config('hotsms.api_token'));

        if (! empty($token)) {
            return array_merge(['api_token' => $token], $params);
        }

        return array_merge([
            'user_name' => $this->setting('hotsms_username', config('hotsms.username')),
            'user_pass' => $this->setting('hotsms_password', config('hotsms.password')),
        ], $params);
    }

    /**
     * Read setting from DB, fall back to config/env.
     */
    private function setting(string $key, mixed $fallback = null): mixed
    {
        try {
            $value = \App\Models\Setting::get($key);

            return $value === null || $value === '' ? $fallback : $value;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function parseResponse(string $raw): array
    {
        if (str_starts_with($raw, self::CODE_SUCCESS)) {
            $messageId = null;
            if (str_contains($raw, '_')) {
                $parts = explode('_', $raw, 2);
                $messageId = $parts[1] ?? null;
            }

            return [
                'ok' => true,
                'code' => self::CODE_SUCCESS,
                'message' => 'تم إرسال الرسالة بنجاح',
                'message_id' => $messageId,
                'raw' => $raw,
            ];
        }

        return [
            'ok' => false,
            'code' => $raw,
            'message' => $this->errorMessageForCode($raw),
            'message_id' => null,
            'raw' => $raw,
        ];
    }

    private function errorMessageForCode(string $code): string
    {
        return match ($code) {
            self::CODE_NO_BALANCE => 'لا يوجد رصيد كافٍ',
            self::CODE_AUTH_ERROR => 'خطأ في بيانات الاعتماد',
            self::CODE_TYPE_ERROR => 'نوع الرسالة غير صحيح',
            self::CODE_MISSING_FIELD => 'بيانات مطلوبة ناقصة',
            self::CODE_UNSUPPORTED_NUMBER => 'رقم الهاتف غير مدعوم',
            self::CODE_SENDER_NOT_REGISTERED => 'اسم المرسل غير مُسجّل',
            self::CODE_IP_NOT_AUTHORIZED => 'الـ IP غير مفوّض',
            self::CODE_API_DISABLED => 'خاصية API غير مفعّلة في حساب Hotsms',
            default => "خطأ غير معروف: {$code}",
        };
    }

    private function disabledResponse(): array
    {
        return [
            'ok' => false,
            'code' => 'disabled',
            'message' => 'خدمة SMS معطّلة',
            'message_id' => null,
            'raw' => '',
        ];
    }
}
