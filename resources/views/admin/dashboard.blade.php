@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@section('breadcrumbs')
    <span class="bc-current">الرئيسية</span>
@endsection

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6">
        @php
            $cards = [
                ['label' => 'إجمالي المستخدمين', 'value' => $stats['users_total'], 'color' => 'blue', 'icon' => 'users'],
                ['label' => 'المستخدمون النشطون', 'value' => $stats['users_active'], 'color' => 'emerald', 'icon' => 'check'],
                ['label' => 'الأنظمة المسجّلة', 'value' => $stats['applications_total'], 'color' => 'violet', 'icon' => 'box'],
                ['label' => 'الجلسات النشطة', 'value' => $stats['sessions_active'], 'color' => 'amber', 'icon' => 'pulse'],
                ['label' => 'دخول اليوم', 'value' => $stats['logins_today'], 'color' => 'sky', 'icon' => 'login'],
                ['label' => 'محاولات فاشلة', 'value' => $stats['failed_today'], 'color' => 'rose', 'icon' => 'alert'],
            ];
            $colors = [
                'blue' => ['bg' => 'rgba(59,130,246,.12)', 'fg' => '#93c5fd'],
                'emerald' => ['bg' => 'rgba(16,185,129,.12)', 'fg' => '#6ee7b7'],
                'violet' => ['bg' => 'rgba(139,92,246,.12)', 'fg' => '#c4b5fd'],
                'amber' => ['bg' => 'rgba(245,158,11,.12)', 'fg' => '#fcd34d'],
                'sky' => ['bg' => 'rgba(14,165,233,.12)', 'fg' => '#7dd3fc'],
                'rose' => ['bg' => 'rgba(244,63,94,.12)', 'fg' => '#fda4af'],
            ];
        @endphp

        @php
            $lightColors = [
                'blue' => ['bg' => '#eff6ff', 'fg' => '#2563eb'],
                'emerald' => ['bg' => '#ecfdf5', 'fg' => '#059669'],
                'violet' => ['bg' => '#f5f3ff', 'fg' => '#7c3aed'],
                'amber' => ['bg' => '#fffbeb', 'fg' => '#d97706'],
                'sky' => ['bg' => '#f0f9ff', 'fg' => '#0284c7'],
                'rose' => ['bg' => '#fff1f2', 'fg' => '#e11d48'],
            ];
        @endphp

        @foreach ($cards as $card)
            @php $c = $lightColors[$card['color']]; @endphp
            <div class="card-glass rounded-2xl p-5 hover:-translate-y-0.5 hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                     style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }}">
                    @switch($card['icon'])
                        @case('users')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @break
                        @case('check')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @break
                        @case('box')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @break
                        @case('pulse')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            @break
                        @case('login')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            @break
                        @case('alert')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @break
                    @endswitch
                </div>
                <p class="text-xs text-slate-500 mb-1">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold text-slate-900">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="card-glass rounded-2xl p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-900 text-sm">محاولات الدخول (آخر 30 يوم)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">ناجحة مقابل فاشلة</p>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> ناجحة</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> فاشلة</span>
                </div>
            </div>
            <div style="height: 240px; position: relative;">
                <canvas id="chart-logins"></canvas>
            </div>
        </div>

        <div class="card-glass rounded-2xl p-5">
            <div class="mb-4">
                <h3 class="font-semibold text-slate-900 text-sm">الأنظمة الأكثر استخداماً</h3>
                <p class="text-xs text-slate-500 mt-0.5">عدد tokens مُصدرة (آخر 30 يوم)</p>
            </div>
            <div style="height: 240px; position: relative;">
                <canvas id="chart-systems"></canvas>
            </div>
        </div>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">آخر محاولات الدخول</h2>
                <p class="text-xs text-slate-500 mt-0.5">آخر 10 محاولات (ناجحة أو فاشلة)</p>
            </div>
        </div>

        @if ($recentLogins->isEmpty())
            <div class="px-6 py-12 text-center text-slate-500 text-sm">لا توجد محاولات دخول بعد.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>الحالة</th>
                            <th>البريد</th>
                            <th>IP</th>
                            <th>السبب</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentLogins as $log)
                            <tr>
                                <td>
                                    @if ($log->event_type === \App\Models\AuditLog::EVENT_LOGIN_SUCCESS)
                                        <span class="badge badge-success">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> نجاح
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> فشل
                                        </span>
                                    @endif
                                </td>
                                <td dir="ltr" class="font-medium text-slate-900">{{ $log->email ?? '—' }}</td>
                                <td dir="ltr" class="font-mono text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                                <td class="text-xs text-slate-500">{{ $log->metadata['reason'] ?? '—' }}</td>
                                <td class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @php $chartData = ['logins' => $charts['logins_30d'], 'systems' => $charts['top_systems']]; @endphp
    <script id="dashboard-charts-data" type="application/json">@json($chartData, JSON_UNESCAPED_UNICODE)</script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const data = JSON.parse(document.getElementById('dashboard-charts-data').textContent);

            Chart.defaults.font.family = "'Tajawal', system-ui, sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#64748b';

            const loginsCanvas = document.getElementById('chart-logins');
            if (loginsCanvas && data.logins.labels.length) {
                new Chart(loginsCanvas, {
                    type: 'line',
                    data: {
                        labels: data.logins.labels.map(d => d.slice(5)),
                        datasets: [
                            { label: 'ناجحة', data: data.logins.success, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', tension: 0.35, fill: true, pointRadius: 2, pointHoverRadius: 5, borderWidth: 2 },
                            { label: 'فاشلة', data: data.logins.failed, borderColor: '#f43f5e', backgroundColor: 'rgba(244,63,94,.1)', tension: 0.35, fill: true, pointRadius: 2, pointHoverRadius: 5, borderWidth: 2 },
                        ],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { rtl: true, backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 } },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 15 } },
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0', drawBorder: false } },
                        },
                    },
                });
            }

            const systemsCanvas = document.getElementById('chart-systems');
            if (systemsCanvas && data.systems.labels.length) {
                new Chart(systemsCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: data.systems.labels,
                        datasets: [{ data: data.systems.data, backgroundColor: data.systems.colors, borderColor: '#fff', borderWidth: 2 }],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '60%',
                        plugins: {
                            legend: { position: 'bottom', rtl: true, labels: { padding: 12, boxWidth: 10, font: { size: 11 } } },
                            tooltip: { rtl: true, backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 },
                        },
                    },
                });
            } else if (systemsCanvas) {
                systemsCanvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-xs text-slate-500">لا توجد بيانات بعد</div>';
            }
        });
    </script>
@endsection
