<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\SsoSession;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users_total' => User::count(),
            'users_active' => User::where('is_active', true)->count(),
            'applications_total' => Application::whereNotNull('secret')->where('revoked', false)->count(),
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

        $charts = [
            'logins_30d' => $this->loginsByDay(30),
            'top_systems' => $this->topSystemsByTokens(30),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentLogins' => $recentLogins,
            'charts' => $charts,
        ]);
    }

    private function loginsByDay(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = AuditLog::query()
            ->selectRaw('DATE(created_at) as day, event_type, COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->whereIn('event_type', [AuditLog::EVENT_LOGIN_SUCCESS, AuditLog::EVENT_LOGIN_FAILED])
            ->groupBy('day', 'event_type')
            ->orderBy('day')
            ->get();

        $byDay = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $byDay[$date] = ['success' => 0, 'failed' => 0];
        }

        foreach ($rows as $row) {
            $date = \Carbon\Carbon::parse($row->day)->format('Y-m-d');
            if (! isset($byDay[$date])) {
                continue;
            }
            $key = $row->event_type === AuditLog::EVENT_LOGIN_SUCCESS ? 'success' : 'failed';
            $byDay[$date][$key] = (int) $row->total;
        }

        return [
            'labels' => array_keys($byDay),
            'success' => array_column(array_values($byDay), 'success'),
            'failed' => array_column(array_values($byDay), 'failed'),
        ];
    }

    private function topSystemsByTokens(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = AuditLog::query()
            ->where('event_type', AuditLog::EVENT_TOKEN_ISSUED)
            ->where('created_at', '>=', $start)
            ->whereNotNull('client_id')
            ->select('client_id', DB::raw('COUNT(*) as total'))
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $clientIds = $rows->pluck('client_id')->all();
        $apps = Application::whereIn('id', $clientIds)->get(['id', 'display_name_ar', 'slug', 'color'])->keyBy('id');

        return [
            'labels' => $rows->map(fn ($r) => optional($apps->get($r->client_id))->display_name_ar ?? optional($apps->get($r->client_id))->slug ?? 'غير معروف')->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
            'colors' => $rows->map(fn ($r) => optional($apps->get($r->client_id))->color ?? '#64748b')->all(),
        ];
    }
}
