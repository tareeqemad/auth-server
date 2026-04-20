@extends('profile._layout')

@section('title', 'سجل نشاطي')

@section('content')
    <div class="card p-5 mb-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-slate-900">سجل الأنشطة</h2>
                <p class="text-xs text-slate-500">آخر الأحداث على حسابك (عمليات الدخول، تغيير كلمة المرور، إلخ)</p>
            </div>
        </div>
    </div>

    @if ($logs->isEmpty())
        <div class="card p-12 text-center">
            <p class="text-slate-500 text-sm">لا يوجد نشاط مسجّل.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="divide-y divide-slate-100">
                @foreach ($logs as $log)
                    @php
                        $info = $eventTypes[$log->event_type] ?? [$log->event_type, 'info'];
                        [$label, $variant] = $info;
                        $icon = match (true) {
                            str_contains($log->event_type, 'login_success') => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                            str_contains($log->event_type, 'login_failed') => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                            str_contains($log->event_type, 'logout') => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                            str_contains($log->event_type, 'password') => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        };
                    @endphp
                    <div class="p-5 flex items-start gap-3 hover:bg-slate-50 transition">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                             @if($variant==='success') bg-emerald-50 text-emerald-600
                             @elseif($variant==='danger') bg-rose-50 text-rose-600
                             @elseif($variant==='warning') bg-amber-50 text-amber-600
                             @else bg-blue-50 text-blue-600 @endif">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 text-sm">{{ $label }}</p>
                            <div class="flex flex-wrap gap-3 mt-1 text-xs text-slate-500">
                                <span dir="ltr" class="font-mono">IP: {{ $log->ip_address ?? '—' }}</span>
                                @if ($log->metadata['reason'] ?? null)
                                    <span>السبب: {{ $log->metadata['reason'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-slate-400 whitespace-nowrap shrink-0">
                            {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
@endsection
