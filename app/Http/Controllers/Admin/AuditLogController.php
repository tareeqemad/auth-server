<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()
            ->with('user:id,full_name,email')
            ->latest();

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

        $logs = $query->paginate(25)->withQueryString();

        $eventTypes = [
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

        return view('admin.audit_logs.index', [
            'logs' => $logs,
            'search' => $search,
            'event' => $event,
            'from' => $from,
            'to' => $to,
            'eventTypes' => $eventTypes,
            'stats' => [
                'total' => AuditLog::count(),
                'today' => AuditLog::whereDate('created_at', today())->count(),
                'failed_today' => AuditLog::whereDate('created_at', today())->where('event_type', AuditLog::EVENT_LOGIN_FAILED)->count(),
            ],
        ]);
    }
}
