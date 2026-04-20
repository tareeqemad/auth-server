<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Event types considered "notable" for admin notifications.
     * Maps event_type → [icon, color, label].
     */
    private const NOTIFIABLE_EVENTS = [
        AuditLog::EVENT_ACCOUNT_LOCKED => ['🔒', 'rose', 'قفل تلقائي لحساب'],
        AuditLog::EVENT_ACCOUNT_LOCKED_BY_ADMIN => ['🛡️', 'amber', 'مدير حظر حساباً'],
        AuditLog::EVENT_ACCOUNT_UNLOCKED_BY_ADMIN => ['🔓', 'emerald', 'مدير فكّ حظراً'],
        AuditLog::EVENT_PASSWORD_REUSE_BLOCKED => ['⚠️', 'amber', 'محاولة إعادة استخدام كلمة مرور'],
        AuditLog::EVENT_BACKCHANNEL_LOGOUT_FAILED => ['🔌', 'rose', 'فشل logout خلفي لنظام عميل'],
        AuditLog::EVENT_PASSWORD_RESET_COMPLETED => ['🔑', 'blue', 'إعادة تعيين كلمة مرور'],
        AuditLog::EVENT_CONSENT_DENIED => ['🚫', 'slate', 'رفض موافقة دخول'],
    ];

    public function index(Request $request): JsonResponse
    {
        $lastRead = $request->session()->get('notifications_last_read_at');

        $rows = AuditLog::with('user:id,full_name,email')
            ->whereIn('event_type', array_keys(self::NOTIFIABLE_EVENTS))
            ->where('created_at', '>=', now()->subDays(7))
            ->latest('created_at')
            ->limit(20)
            ->get();

        $items = $rows->map(function (AuditLog $log) use ($lastRead) {
            [$icon, $color, $label] = self::NOTIFIABLE_EVENTS[$log->event_type] ?? ['📋', 'slate', $log->event_type];

            return [
                'id' => $log->id,
                'icon' => $icon,
                'color' => $color,
                'label' => $label,
                'event_type' => $log->event_type,
                'user_name' => $log->user?->full_name ?? $log->email,
                'user_email' => $log->user?->email ?? $log->email,
                'ip_address' => $log->ip_address,
                'metadata' => $log->metadata,
                'created_at_iso' => $log->created_at->toIso8601String(),
                'created_at_human' => $log->created_at->diffForHumans(),
                'is_new' => $lastRead ? $log->created_at->greaterThan($lastRead) : true,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'unread' => $items->where('is_new', true)->count(),
            'items' => $items->values(),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->session()->put('notifications_last_read_at', now()->toIso8601String());

        return response()->json(['success' => true]);
    }
}
