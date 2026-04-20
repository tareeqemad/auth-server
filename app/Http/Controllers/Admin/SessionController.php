<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SsoSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $query = SsoSession::query()
            ->with('user:id,full_name,email')
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->latest('last_activity_at');

        if ($search = $request->query('q')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.sessions.index', [
            'sessions' => $query->paginate(25)->withQueryString(),
            'search' => $search,
            'stats' => [
                'active' => SsoSession::where('revoked', false)->where('expires_at', '>', now())->count(),
                'expired' => SsoSession::where('expires_at', '<=', now())->count(),
                'revoked' => SsoSession::where('revoked', true)->count(),
            ],
        ]);
    }

    public function revoke(Request $request, SsoSession $session): JsonResponse
    {
        $session->update([
            'revoked' => true,
            'revoked_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $session->user_id,
            'event_type' => AuditLog::EVENT_LOGOUT,
            'email' => $session->user?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'revoked_by_admin' => auth()->user()->email,
                'sso_session_id' => $session->id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء الجلسة بنجاح.',
        ]);
    }
}
