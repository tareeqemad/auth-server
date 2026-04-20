<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    private function defaults(): array
    {
        return [
        // Branding
        [
            'key' => 'company_name_ar',
            'value' => 'شركة توزيع كهرباء غزة',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اسم الشركة (عربي)',
            'is_public' => true,
        ],
        [
            'key' => 'company_name_en',
            'value' => 'Gaza Electricity Distribution Company',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اسم الشركة (إنجليزي)',
            'is_public' => true,
        ],
        [
            'key' => 'system_name_ar',
            'value' => 'النظام الموحّد',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اسم النظام (عربي)',
            'is_public' => true,
        ],
        [
            'key' => 'system_name_en',
            'value' => 'Unified System',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اسم النظام (إنجليزي)',
            'is_public' => true,
        ],
        [
            'key' => 'system_tagline',
            'value' => 'بوابة الدخول الموحّد لجميع خدمات الشركة',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'الشعار التعريفي',
            'is_public' => true,
        ],
        [
            'key' => 'logo_url',
            'value' => null,
            'group' => 'branding',
            'type' => 'string',
            'label' => 'رابط الشعار',
            'description' => 'اتركه فارغًا لاستخدام الأيقونة الافتراضية',
            'is_public' => true,
        ],
        [
            'key' => 'primary_color',
            'value' => '#0f4c81',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اللون الأساسي',
            'is_public' => true,
        ],
        [
            'key' => 'accent_color',
            'value' => '#f59e0b',
            'group' => 'branding',
            'type' => 'string',
            'label' => 'اللون الثانوي',
            'is_public' => true,
        ],

        // Contact
        [
            'key' => 'support_email',
            'value' => 'support@gedco.ps',
            'group' => 'contact',
            'type' => 'string',
            'label' => 'بريد الدعم الفني',
            'is_public' => true,
        ],
        [
            'key' => 'support_phone',
            'value' => '+970 8 286 0600',
            'group' => 'contact',
            'type' => 'string',
            'label' => 'هاتف الدعم الفني',
            'is_public' => true,
        ],

        // Security
        [
            'key' => 'sso_session_ttl_hours',
            'value' => '8',
            'group' => 'security',
            'type' => 'integer',
            'label' => 'مدة جلسة SSO (بالساعات)',
            'is_public' => false,
        ],
        [
            'key' => 'password_min_length',
            'value' => '8',
            'group' => 'security',
            'type' => 'integer',
            'label' => 'الحد الأدنى لطول كلمة المرور',
            'is_public' => false,
        ],
        [
            'key' => 'max_failed_login_attempts',
            'value' => '5',
            'group' => 'security',
            'type' => 'integer',
            'label' => 'عدد محاولات الدخول الفاشلة المسموحة',
            'is_public' => false,
        ],

        // SMS Gateway (Hotsms)
        [
            'key' => 'hotsms_enabled',
            'value' => '1',
            'group' => 'sms',
            'type' => 'boolean',
            'label' => 'تفعيل إرسال SMS',
            'description' => 'عند الإيقاف لن تُرسَل أي رسائل نصية',
            'is_public' => false,
        ],
        [
            'key' => 'hotsms_username',
            'value' => env('HOTSMS_USERNAME', ''),
            'group' => 'sms',
            'type' => 'string',
            'label' => 'اسم المستخدم (Hotsms)',
            'is_public' => false,
        ],
        [
            'key' => 'hotsms_password',
            'value' => env('HOTSMS_PASSWORD', ''),
            'group' => 'sms',
            'type' => 'password',
            'label' => 'كلمة المرور (Hotsms)',
            'description' => 'اتركه فارغاً عند التعديل للإبقاء على القيمة الحالية',
            'is_public' => false,
        ],
        [
            'key' => 'hotsms_sender',
            'value' => env('HOTSMS_SENDER', 'E-SER-GEDCO'),
            'group' => 'sms',
            'type' => 'string',
            'label' => 'اسم المرسل',
            'description' => 'يجب أن يكون مُسجّلاً في حساب Hotsms',
            'is_public' => false,
        ],
    ];
    }

    public function run(): void
    {
        foreach ($this->defaults() as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'label' => $setting['label'] ?? null,
                    'description' => $setting['description'] ?? null,
                    'is_public' => $setting['is_public'] ?? false,
                ],
            );
        }

        $this->command->info('✅ تم تعبئة '.count($this->defaults()).' إعداد افتراضي.');
    }
}
