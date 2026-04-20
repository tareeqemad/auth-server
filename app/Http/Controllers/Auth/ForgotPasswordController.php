<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(['email' => $data['email']]);

        AuditLog::create([
            'event_type' => AuditLog::EVENT_PASSWORD_RESET_REQUESTED,
            'email' => $data['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['status' => $status],
        ]);

        return back()->with(
            'status',
            'إن كان البريد مسجّلًا لدينا، فسيصلك رابط إعادة التعيين خلال دقائق.',
        );
    }
}
