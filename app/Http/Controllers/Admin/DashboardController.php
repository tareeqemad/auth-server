<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SsoSession;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Laravel\Passport\Client;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users_total' => User::count(),
            'users_active' => User::where('is_active', true)->count(),
            'applications_total' => Client::whereNotNull('secret')->where('revoked', false)->count(),
            'sessions_active' => SsoSession::where('revoked', false)
                ->where('expires_at', '>', now())
                ->count(),
            'logins_today' => AuditLog::where('event_type', AuditLog::EVENT_LOGIN_SUCCESS)
                ->where('created_at', '>=', today())
                ->count(),
            'failed_today' => AuditLog::where('event_type', AuditLog::EVENT_LOGIN_FAILED)
                ->where('created_at', '>=', today())
                ->count(),
        ];

        $recentLogins = AuditLog::whereIn('event_type', [
                AuditLog::EVENT_LOGIN_SUCCESS,
                AuditLog::EVENT_LOGIN_FAILED,
            ])
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentLogins' => $recentLogins,
        ]);
    }
}
