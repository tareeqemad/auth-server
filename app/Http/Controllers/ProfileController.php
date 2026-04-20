<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SsoSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.info', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at')],
        ], [], [
            'full_name' => 'الاسم الكامل',
            'phone' => 'الهاتف',
            'email' => 'البريد الإلكتروني',
        ]);

        $emailChanged = $user->email !== $data['email'];

        $user->update([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التعديلات.',
            ]);
        }

        return back()->with('status', 'تم الحفظ.');
    }

    public function showPassword(): View
    {
        return view('profile.password', [
            'user' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'password.required' => 'كلمة المرور الجديدة مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
            'password.different' => 'كلمة المرور الجديدة يجب أن تختلف عن الحالية',
        ]);

        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة المرور الحالية غير صحيحة',
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_PASSWORD_CHANGED,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['source' => 'self_service'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح.',
            ]);
        }

        return back()->with('status', 'تم التغيير.');
    }

    public function sessions(Request $request): View
    {
        $currentSessionId = $request->session()->get('sso_session_id');

        $sessions = SsoSession::query()
            ->where('user_id', Auth::id())
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->latest('last_activity_at')
            ->get();

        return view('profile.sessions', [
            'user' => Auth::user(),
            'sessions' => $sessions,
            'currentSessionId' => $currentSessionId,
        ]);
    }

    public function revokeSession(Request $request, SsoSession $session): JsonResponse
    {
        if ($session->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'جلسة غير متاحة.'], 403);
        }

        if ($session->id === $request->session()->get('sso_session_id')) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إنهاء جلستك الحالية. استخدم تسجيل الخروج بدلاً من ذلك.'], 422);
        }

        $session->update([
            'revoked' => true,
            'revoked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء الجلسة.',
        ]);
    }

    public function revokeAllSessions(Request $request): JsonResponse
    {
        $currentSessionId = $request->session()->get('sso_session_id');

        $count = SsoSession::where('user_id', Auth::id())
            ->where('revoked', false)
            ->where('id', '!=', $currentSessionId)
            ->update([
                'revoked' => true,
                'revoked_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "تم إنهاء {$count} جلسة.",
        ]);
    }

    public function security(): View
    {
        return view('profile.security', [
            'user' => Auth::user(),
        ]);
    }

    public function activity(Request $request): View
    {
        $logs = AuditLog::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        $eventTypes = [
            AuditLog::EVENT_LOGIN_SUCCESS => ['دخول ناجح', 'success'],
            AuditLog::EVENT_LOGIN_FAILED => ['محاولة دخول فاشلة', 'danger'],
            AuditLog::EVENT_LOGOUT => ['تسجيل خروج', 'info'],
            AuditLog::EVENT_PASSWORD_RESET_REQUESTED => ['طلب إعادة تعيين كلمة المرور', 'warning'],
            AuditLog::EVENT_PASSWORD_RESET_COMPLETED => ['تم إعادة تعيين كلمة المرور', 'success'],
            AuditLog::EVENT_PASSWORD_CHANGED => ['تغيير كلمة المرور', 'warning'],
            AuditLog::EVENT_TOKEN_ISSUED => ['وصول لنظام', 'info'],
        ];

        return view('profile.activity', [
            'user' => Auth::user(),
            'logs' => $logs,
            'eventTypes' => $eventTypes,
        ]);
    }
}
