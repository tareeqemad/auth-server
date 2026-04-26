<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = $this->buildQuery($request)
            ->with('user:id,full_name,email')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit_logs.index', [
            'logs' => $logs,
            'search' => $request->query('q'),
            'event' => $request->query('event'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'eventTypes' => $this->eventTypes(),
            'stats' => [
                'total' => AuditLog::count(),
                'today' => AuditLog::whereDate('created_at', today())->count(),
                'failed_today' => AuditLog::whereDate('created_at', today())->where('event_type', AuditLog::EVENT_LOGIN_FAILED)->count(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'audit-logs-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'id', 'event_type', 'email', 'user_id', 'ip_address',
                'user_agent', 'client_id', 'metadata', 'created_at',
            ]);

            $this->buildQuery($request)
                ->orderBy('created_at')
                ->chunk(1000, function ($logs) use ($out) {
                    foreach ($logs as $log) {
                        fputcsv($out, [
                            $log->id, $log->event_type, $log->email, $log->user_id,
                            $log->ip_address, $log->user_agent, $log->client_id,
                            is_array($log->metadata) ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $log->metadata,
                            $log->created_at?->toIso8601String() ?? '',
                        ]);
                    }
                });

            fclose($out);
        }, $filename, $headers);
    }

    private function buildQuery(Request $request)
    {
        $query = AuditLog::query()->latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($event = $request->query('event')) {
            $query->where('event_type', $event);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    private function eventTypes(): array
    {
        return [
            AuditLog::EVENT_LOGIN_SUCCESS => 'دخول ناجح',
            AuditLog::EVENT_LOGIN_FAILED => 'دخول فاشل',
            AuditLog::EVENT_LOGOUT => 'تسجيل خروج',
            AuditLog::EVENT_PASSWORD_RESET_REQUESTED => 'طلب إعادة تعيين كلمة المرور',
            AuditLog::EVENT_PASSWORD_RESET_COMPLETED => 'تم إعادة تعيين كلمة المرور',
            AuditLog::EVENT_PASSWORD_CHANGED => 'تغيير كلمة المرور',
            AuditLog::EVENT_TOKEN_ISSUED => 'إصدار token',
            AuditLog::EVENT_TOKEN_REVOKED => 'إبطال token',
            AuditLog::EVENT_CONSENT_GRANTED => 'موافقة على وصول',
            AuditLog::EVENT_CONSENT_DENIED => 'رفض وصول',
        ];
    }
}
