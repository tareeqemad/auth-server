<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SsoSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $ssoSessionId = $request->session()->get('sso_session_id');

            if ($ssoSessionId) {
                SsoSession::where('id', $ssoSessionId)
                    ->update([
                        'revoked' => true,
                        'revoked_at' => now(),
                    ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_LOGOUT,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['sso_session_id' => $ssoSessionId],
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
