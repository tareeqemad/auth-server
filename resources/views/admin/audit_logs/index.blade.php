@extends('layouts.admin')

@section('title', 'سجل الأحداث')

@section('breadcrumbs')
    <span class="bc-current">سجل الأحداث</span>
@endsection

@section('content')
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">الإجمالي</p>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 text-violet-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">اليوم</p>
                    <p class="text-3xl font-bold text-sky-600">{{ $stats['today'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-sky-50 text-sky-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">فشل اليوم</p>
                    <p class="text-3xl font-bold text-rose-600">{{ $stats['failed_today'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <form method="GET" class="flex gap-2 flex-wrap items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">بحث</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="بريد أو IP..."
                           class="input-glass w-full px-3 py-2 rounded-lg text-sm">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">نوع الحدث</label>
                    <select name="event" class="input-glass w-full px-3 py-2 rounded-lg text-sm">
                        <option value="">كل الأحداث</option>
                        @foreach ($eventTypes as $key => $label)
                            <option value="{{ $key }}" @selected($event === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">من</label>
                    <input type="date" name="from" value="{{ $from }}" class="input-glass px-3 py-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">إلى</label>
                    <input type="date" name="to" value="{{ $to }}" class="input-glass px-3 py-2 rounded-lg text-sm">
                </div>
                <button type="submit" class="btn-ghost text-sm px-4 py-2 rounded-lg">فلترة</button>
                @if ($search || $event || $from || $to)
                    <a href="{{ route('admin.audit_logs.index') }}" class="btn-ghost text-sm px-4 py-2 rounded-lg">إعادة تعيين</a>
                @endif
            </form>
        </div>

        @if ($logs->isEmpty())
            <div class="px-6 py-16 text-center text-slate-500 text-sm">لا توجد سجلات تطابق الفلاتر.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>الحدث</th>
                            <th>المستخدم</th>
                            <th>IP</th>
                            <th>User Agent</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    @php $type = $eventTypes[$log->event_type] ?? $log->event_type; @endphp
                                    @if ($log->event_type === \App\Models\AuditLog::EVENT_LOGIN_FAILED)
                                        <span class="badge badge-danger">{{ $type }}</span>
                                    @elseif ($log->event_type === \App\Models\AuditLog::EVENT_LOGIN_SUCCESS)
                                        <span class="badge badge-success">{{ $type }}</span>
                                    @elseif (str_contains($log->event_type, 'password'))
                                        <span class="badge badge-warning">{{ $type }}</span>
                                    @else
                                        <span class="badge badge-info">{{ $type }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->email)
                                        <p class="text-slate-900 text-sm" dir="ltr">{{ $log->email }}</p>
                                        @if ($log->user)
                                            <p class="text-xs text-slate-500">{{ $log->user->full_name }}</p>
                                        @endif
                                    @else — @endif
                                </td>
                                <td class="font-mono text-xs text-slate-500" dir="ltr">{{ $log->ip_address ?? '—' }}</td>
                                <td class="text-xs text-slate-500 truncate max-w-[200px]" title="{{ $log->user_agent }}">{{ Str::limit($log->user_agent, 35) ?? '—' }}</td>
                                <td class="text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
