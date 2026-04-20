<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.index', [
            'groups' => $settings,
        ]);
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            $setting = Setting::find($key);

            if ($setting && $setting->type === 'password' && ($value === null || $value === '')) {
                continue;
            }

            if ($setting && $setting->type === 'boolean') {
                $value = $request->boolean("settings.$key") ? '1' : '0';
            }

            Setting::set($key, $value ?? '');
        }

        if ($request->has('_boolean_keys')) {
            foreach (explode(',', $request->input('_boolean_keys')) as $bk) {
                if ($bk === '') continue;
                if (! isset($data['settings'][$bk])) {
                    Setting::set($bk, '0');
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإعدادات بنجاح.',
            ]);
        }

        return back()->with('status', 'تم الحفظ.');
    }

    public function testSms(Request $request, \App\Services\HotsmsService $sms): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        $result = $sms->send(
            $data['phone'],
            'GEDCO SSO: رسالة تجريبية من لوحة التحكم. إن وصلتك فهذا يعني أن إعدادات SMS صحيحة.',
        );

        return response()->json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'code' => $result['code'],
        ], $result['ok'] ? 200 : 422);
    }

    public function smsBalance(\App\Services\HotsmsService $sms): JsonResponse
    {
        $result = $sms->balance();

        return response()->json($result);
    }
}
