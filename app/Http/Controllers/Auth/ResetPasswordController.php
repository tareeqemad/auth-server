<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function show(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PasswordReset) {
            AuditLog::create([
                'event_type' => AuditLog::EVENT_PASSWORD_RESET_COMPLETED,
                'email' => $data['email'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['status' => $status, 'success' => false],
            ]);

            return back()->withErrors(['email' => __($status)]);
        }

        $user = User::where('email', $data['email'])->first();

        AuditLog::create([
            'user_id' => $user?->id,
            'event_type' => AuditLog::EVENT_PASSWORD_RESET_COMPLETED,
            'email' => $data['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['status' => $status, 'success' => true],
        ]);

        return redirect()->route('login')->with(
            'status',
            'تم تغيير كلمة المرور بنجاح. بإمكانك تسجيل الدخول الآن.',
        );
    }
}
