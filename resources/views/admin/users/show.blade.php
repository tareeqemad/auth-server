@extends('layouts.admin')

@section('title', 'استعلام: '.$user->full_name)

@section('breadcrumbs')
    <a href="{{ route('admin.users.index') }}" class="bc-link">المستخدمون</a>
    <span class="bc-sep">›</span>
    <span class="bc-current">{{ $user->full_name }}</span>
@endsection

@section('content')
    <style>
        .hero-banner {
            background: linear-gradient(135deg, #0F2440 0%, #1e3a5f 50%, #0F2440 100%);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            color: white;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(251,191,36,.18), transparent 60%);
            pointer-events: none;
        }
        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(249,115,22,.12), transparent 60%);
            pointer-events: none;
        }
        .avatar-xl {
            width: 96px; height: 96px;
            border-radius: 24px;
            background: linear-gradient(135deg, #F97316, #FBBF24);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 2.25rem; color: white;
            box-shadow: 0 10px 30px rgba(251,191,36,.35);
            border: 4px solid rgba(255,255,255,.15);
            position: relative;
            flex-shrink: 0;
        }
        .avatar-xl .status-dot {
            position: absolute;
            bottom: 4px; left: 4px;
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 3px solid #0F2440;
        }
        .hero-chip {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .35rem .75rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 999px;
            font-size: 11px; font-weight: 600;
            color: rgba(255,255,255,.85);
        }
        .hero-chip.on { background: rgba(34,197,94,.18); border-color: rgba(34,197,94,.35); color: #86efac; }
        .hero-chip.off { background: rgba(244,63,94,.18); border-color: rgba(244,63,94,.35); color: #fda4af; }
        .hero-chip.warn { background: rgba(251,191,36,.18); border-color: rgba(251,191,36,.35); color: #fcd34d; }
        .hero-chip.info { background: rgba(59,130,246,.18); border-color: rgba(59,130,246,.35); color: #93c5fd; }

        .hero-copy-line {
            display: flex; align-items: center; gap: .5rem;
            padding: .5rem .75rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            font-size: 12px;
            cursor: pointer;
            transition: all .15s;
        }
        .hero-copy-line:hover { background: rgba(255,255,255,.1); transform: translateY(-1px); }
        .hero-copy-line .icn { color: #fbbf24; opacity:.8 }
        .hero-copy-line .val { font-family: monospace; flex: 1; color: white; }

        .stat-mini {
            background: white; border-radius: 14px;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            transition: all .15s;
        }
        .stat-mini:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,.04); }
        .stat-mini .lbl { font-size: 11px; color: #64748b; margin-bottom: .2rem; }
        .stat-mini .val { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-mini .ico {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .section-card {
            background: white; border: 1px solid #e2e8f0;
            border-radius: 16px; overflow: hidden;
        }
        .section-head {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-title {
            font-weight: 700; color: #0f172a; font-size: 14px;
            display: flex; align-items: center; gap: .5rem;
        }
        .section-body { padding: 1.25rem; }
        .kv-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: .7rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .kv-row:last-child { border-bottom: 0; }
        .kv-row .key { font-size: 12px; color: #64748b; }
        .kv-row .val { font-size: 13px; color: #0f172a; font-weight: 500; }

        .action-btn {
            display: flex; align-items: center; gap: .65rem;
            padding: .7rem 1rem; border-radius: 10px;
            border: 1px solid #e2e8f0; background: white;
            font-size: 13px; font-weight: 600; color: #0f172a;
            cursor: pointer; transition: all .15s;
            width: 100%; text-align: right;
        }
        .action-btn:hover { border-color: #cbd5e1; background: #f8fafc; transform: translateY(-1px); }
        .action-btn.danger { color: #be123c; }
        .action-btn.danger:hover { background: #fff1f2; border-color: #fecdd3; }
        .action-btn.warn { color: #b45309; }
        .action-btn.warn:hover { background: #fffbeb; border-color: #fde68a; }
        .action-btn.success { color: #047857; }
        .action-btn.success:hover { background: #ecfdf5; border-color: #a7f3d0; }

        .timeline {
            position: relative;
            padding-right: 1.5rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            right: 8px; top: 6px; bottom: 6px;
            width: 2px; background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            right: -14px; top: 6px;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: white;
            border: 2px solid #fbbf24;
        }
        .timeline-item.success::before { border-color: #10b981; }
        .timeline-item.danger::before { border-color: #f43f5e; }
        .timeline-item.info::before { border-color: #3b82f6; }
        .timeline-item .evt {
            display: inline-block;
            font-size: 11px; font-family: monospace;
            padding: .2rem .6rem; border-radius: 5px;
            background: #f1f5f9; color: #475569;
        }
        .timeline-item .meta { font-size: 11px; color: #94a3b8; margin-top: .25rem; }

        .system-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            display: flex; align-items: center; gap: .75rem;
            transition: all .15s;
        }
        .system-card:hover { border-color: #cbd5e1; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.06); }
        .system-card .sys-init {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: white; font-size: 16px;
        }
    </style>

    {{-- ═════════════════ HERO BANNER ═════════════════ --}}
    @php
        $statusColor = $user->isLocked() ? '#f43f5e' : ($user->is_active ? '#22c55e' : '#64748b');
        $eventColors = [
            'login_success' => 'success',
            'login_failed' => 'danger',
            'logout' => 'info',
            'password_changed' => 'info',
            'password_reset_completed' => 'info',
            'account_locked' => 'danger',
            'account_locked_by_admin' => 'danger',
            'account_unlocked_by_admin' => 'success',
        ];
    @endphp

    <div class="hero-banner mb-5">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start gap-6">
            {{-- Avatar --}}
            <div class="avatar-xl">
                {{ mb_substr($user->full_name, 0, 1) }}
                <span class="status-dot" style="background: {{ $statusColor }};"></span>
            </div>

            {{-- Identity + badges --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    @if ($user->source === \App\Models\User::SOURCE_HR_MASTER)
                        <span class="hero-chip info">👥 موظف</span>
                    @elseif ($user->source === \App\Models\User::SOURCE_EXTERNAL)
                        <span class="hero-chip warn">🤝 خارجي</span>
                    @endif

                    @if ($user->is_active)
                        <span class="hero-chip on">● نشط</span>
                    @else
                        <span class="hero-chip off">● معطّل</span>
                    @endif

                    @if ($user->isLocked())
                        <span class="hero-chip off">🔒 محظور</span>
                    @endif

                    @if ($user->needs_id_linking)
                        <span class="hero-chip warn">⚠️ بحاجة ربط هوية</span>
                    @endif

                    @if ($user->sms_2fa_enabled)
                        <span class="hero-chip on">🔐 2FA</span>
                    @endif

                    @if ($user->employee_number)
                        <span class="hero-chip" style="background:rgba(251,191,36,.22);border-color:rgba(251,191,36,.4);color:#fcd34d">
                            #{{ $user->employee_number }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold mb-1">{{ $user->full_name }}</h1>
                @if ($user->job_title)
                    <p class="text-sm" style="color: rgba(255,255,255,.6)">{{ $user->job_title }}{{ $user->department ? ' — '.$user->department : '' }}</p>
                @endif

                {{-- Quick copy strip --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4">
                    <div class="hero-copy-line" data-copy="{{ $user->email }}" title="انسخ">
                        <svg class="icn w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="val" dir="ltr">{{ $user->email }}</span>
                    </div>
                    <div class="hero-copy-line" data-copy="{{ $user->phone }}" title="انسخ">
                        <svg class="icn w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span class="val" dir="ltr">{{ $user->phone ?: '— بدون جوال' }}</span>
                    </div>
                    <div class="hero-copy-line" data-copy="{{ $user->national_id }}" title="انسخ">
                        <svg class="icn w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0"/></svg>
                        <span class="val" dir="ltr">{{ $user->national_id ?: '— بدون هوية' }}</span>
                    </div>
                </div>
            </div>

            {{-- Hero actions --}}
            <div class="flex flex-row lg:flex-col gap-2 shrink-0">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm text-white shadow"
                   style="background: linear-gradient(135deg,#F97316,#FBBF24)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    تعديل
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-sm"
                   style="background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); color: rgba(255,255,255,.9)">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    رجوع
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════ QUICK STATS ═════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="stat-mini">
            <div>
                <p class="lbl">الجلسات النشطة</p>
                <p class="val">{{ $sessionsActive }}</p>
            </div>
            <div class="ico" style="background:#dbeafe;color:#1d4ed8">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-mini">
            <div>
                <p class="lbl">الأنظمة</p>
                <p class="val">{{ $user->systemLinks->count() }}</p>
            </div>
            <div class="ico" style="background:#fef3c7;color:#b45309">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="stat-mini">
            <div>
                <p class="lbl">محاولات فاشلة</p>
                <p class="val {{ $user->failed_login_attempts > 0 ? 'text-amber-600' : '' }}">{{ $user->failed_login_attempts }}</p>
            </div>
            <div class="ico" style="background:{{ $user->failed_login_attempts > 0 ? '#fee2e2' : '#f1f5f9' }};color:{{ $user->failed_login_attempts > 0 ? '#be123c' : '#64748b' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-mini">
            <div>
                <p class="lbl">آخر دخول</p>
                <p class="val" style="font-size:12px">{{ $user->last_login_at?->diffForHumans() ?? '— لم يسجّل' }}</p>
            </div>
            <div class="ico" style="background:#dcfce7;color:#15803d">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- ═════════════════ MAIN GRID ═════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Main column (2/3) ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Employment info --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        بيانات التوظيف
                    </div>
                </div>
                <div class="section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                    <div class="kv-row"><span class="key">الرقم الوظيفي</span><span class="val font-mono">#{{ $user->employee_number ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">المسمى الوظيفي</span><span class="val">{{ $user->job_title ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">القسم</span><span class="val">{{ $user->department ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">الدائرة</span><span class="val">{{ $user->directorate ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">المحافظة</span><span class="val">{{ $user->governorate ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">المصدر</span><span class="val"><code class="text-[11px] bg-slate-100 px-2 py-0.5 rounded">{{ $user->source }}</code></span></div>
                </div>
            </div>

            {{-- Linked systems --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        الأنظمة المرتبطة
                    </div>
                    <span class="badge badge-info">{{ $user->systemLinks->count() }}</span>
                </div>
                <div class="section-body">
                    @if ($user->systemLinks->isEmpty())
                        <div class="text-center py-6">
                            <div class="inline-flex w-14 h-14 rounded-2xl items-center justify-center bg-slate-50 text-slate-400 mb-3">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="text-sm text-slate-500">لم يتم ربطه بأي نظام بعد.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($user->systemLinks as $link)
                                <div class="system-card">
                                    <div class="sys-init" style="background: linear-gradient(135deg, #{{ substr(md5($link->system_name), 0, 6) }}, #{{ substr(md5($link->system_name), 6, 6) }})">
                                        {{ mb_substr($link->system_name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-900 text-sm truncate">{{ $link->system_name }}</p>
                                        <p class="text-[11px] text-slate-500" dir="ltr">ext: {{ $link->external_user_id }}</p>
                                    </div>
                                    <span class="text-[10px] text-slate-400 shrink-0">{{ $link->last_accessed_at?->diffForHumans() ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent activity timeline --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        آخر الأنشطة
                    </div>
                    <span class="text-xs text-slate-500">{{ $recentAudit->count() }} حدث</span>
                </div>
                <div class="section-body">
                    @if ($recentAudit->isEmpty())
                        <p class="text-sm text-slate-400 text-center py-4">لا يوجد نشاط مسجّل.</p>
                    @else
                        <div class="timeline">
                            @foreach ($recentAudit as $log)
                                @php $cls = $eventColors[$log->event_type] ?? ''; @endphp
                                <div class="timeline-item {{ $cls }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="evt">{{ $log->event_type }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($log->ip_address)
                                        <div class="meta" dir="ltr">IP: {{ $log->ip_address }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Side column (1/3) ── --}}
        <div class="space-y-5">

            {{-- Admin actions panel --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        إجراءات سريعة
                    </div>
                </div>
                <div class="section-body space-y-2">
                    <button type="button" class="action-btn warn" data-action="reset-password" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        إعادة تعيين كلمة المرور
                    </button>

                    @if ($user->isLocked() || $user->failed_login_attempts > 0)
                        <button type="button" class="action-btn success" data-action="unlock" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            فك الحظر
                        </button>
                    @else
                        <button type="button" class="action-btn danger" data-action="lock" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            حظر الحساب
                        </button>
                    @endif

                    <button type="button" class="action-btn {{ $user->is_active ? 'warn' : 'success' }}" data-action="toggle-active" data-id="{{ $user->id }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            @if ($user->is_active)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            @endif
                        </svg>
                        {{ $user->is_active ? 'إيقاف الحساب' : 'تفعيل الحساب' }}
                    </button>

                    <button type="button" class="action-btn danger" data-action="delete" data-id="{{ $user->id }}" data-name="{{ $user->full_name }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        حذف نهائياً
                    </button>
                </div>
            </div>

            {{-- Security details --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        الأمان
                    </div>
                </div>
                <div class="section-body">
                    <div class="kv-row">
                        <span class="key">SMS 2FA</span>
                        <span class="val">
                            @if ($user->sms_2fa_enabled)
                                <span class="text-emerald-600 font-bold">✓ مفعّل</span>
                            @else
                                <span class="text-slate-400">— معطّل</span>
                            @endif
                        </span>
                    </div>
                    <div class="kv-row">
                        <span class="key">حالة القفل</span>
                        <span class="val">
                            @if ($user->isLocked())
                                <span class="text-rose-600 font-bold">🔒 محظور</span>
                            @else
                                <span class="text-emerald-600">✓ آمن</span>
                            @endif
                        </span>
                    </div>
                    @if ($user->isLocked())
                        <div class="kv-row"><span class="key">حتى</span><span class="val font-mono text-xs" dir="ltr">{{ $user->locked_until->format('Y-m-d H:i') }}</span></div>
                        @if ($user->locked_reason)
                            <div class="kv-row"><span class="key">السبب</span><span class="val text-xs">{{ $user->locked_reason }}</span></div>
                        @endif
                    @endif
                    <div class="kv-row"><span class="key">آخر IP</span><span class="val font-mono text-xs" dir="ltr">{{ $user->last_login_ip ?? '—' }}</span></div>
                    <div class="kv-row"><span class="key">تاريخ الإنشاء</span><span class="val text-xs" dir="ltr">{{ $user->created_at->format('Y-m-d') }}</span></div>
                </div>
            </div>

            {{-- Technical info --}}
            <div class="section-card">
                <div class="section-head">
                    <div class="section-title">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        تقني
                    </div>
                </div>
                <div class="section-body">
                    <div class="kv-row">
                        <span class="key">UUID</span>
                        <span class="val">
                            <button type="button" class="copy-chip text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200"
                                    data-copy="{{ $user->id }}" dir="ltr">{{ Str::of($user->id)->limit(16, '...') }}</button>
                        </span>
                    </div>
                    @if (! $user->roles->isEmpty())
                        <div class="kv-row">
                            <span class="key">الأدوار</span>
                            <span class="val">
                                @foreach ($user->roles as $role)
                                    <code class="text-[10px] bg-amber-50 text-amber-800 px-2 py-0.5 rounded">{{ $role->name }}</code>
                                @endforeach
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ═════ Copy helpers ═════
            $(document).on('click', '[data-copy]', function () {
                var val = $(this).data('copy');
                if (!val) return;
                navigator.clipboard.writeText(val);
                toastr.success('تم النسخ: ' + val.substring(0, 30));
            });

            // ═════ Admin actions ═════
            $(document).on('click', '[data-action="reset-password"]', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'إعادة تعيين كلمة المرور؟',
                    html: 'سيتم توليد كلمة مرور جديدة للمستخدم <strong>' + name + '</strong>.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، ولّد', cancelButtonText: 'إلغاء'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/reset-password', type: 'POST', dataType: 'json' })
                        .done(function (d) {
                            Swal.fire({
                                title: d.message,
                                html: '<p style="color:rgba(255,255,255,.7);margin:1rem 0">كلمة المرور الجديدة:</p>' +
                                      '<input readonly value="' + d.password + '" dir="ltr" style="width:100%;padding:.6rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;text-align:center">' +
                                      '<button onclick="navigator.clipboard.writeText(\'' + d.password + '\'); toastr.success(\'تم النسخ\')" style="margin-top:.75rem;padding:.5rem 1rem;background:linear-gradient(135deg,#F97316,#FBBF24);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer">نسخ</button>',
                                icon: 'success'
                            });
                        })
                        .fail(function () { toastr.error('فشلت العملية'); });
                });
            });

            $(document).on('click', '[data-action="unlock"]', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'فك الحظر؟',
                    html: 'السماح لـ <strong>' + name + '</strong> بالدخول فوراً.',
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: 'نعم، فك الحظر', cancelButtonText: 'إلغاء'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/unlock', type: 'POST', dataType: 'json' })
                        .done(function (d) { toastr.success(d.message); setTimeout(function(){location.reload();}, 700); })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
                });
            });

            $(document).on('click', '[data-action="lock"]', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'حظر الحساب',
                    html: '<p style="margin-bottom:1rem">حظر <strong>' + name + '</strong>.</p>' +
                          '<input id="swal-minutes" type="number" min="1" placeholder="المدة بالدقائق (اتركها فارغة للحظر الدائم)" style="width:100%;padding:.5rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;color:white;margin-bottom:.5rem">' +
                          '<input id="swal-reason" type="text" placeholder="السبب (اختياري)" style="width:100%;padding:.5rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;color:white">',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، احظر', cancelButtonText: 'إلغاء',
                    preConfirm: function () {
                        return { minutes: $('#swal-minutes').val() || null, reason: $('#swal-reason').val() || null };
                    }
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/lock', type: 'POST', dataType: 'json', data: r.value })
                        .done(function (d) { toastr.success(d.message); setTimeout(function(){location.reload();}, 700); })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
                });
            });

            $(document).on('click', '[data-action="toggle-active"]', function () {
                var id = $(this).data('id');
                $.ajax({ url: '/admin/users/' + id + '/toggle-active', type: 'POST', dataType: 'json' })
                    .done(function (d) { toastr.success(d.message); setTimeout(function(){location.reload();}, 700); })
                    .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
            });

            $(document).on('click', '[data-action="delete"]', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'حذف نهائي!',
                    html: 'هل أنت متأكد من حذف <strong>' + name + '</strong>؟<br><small style="color:#f87171">لا يمكن التراجع.</small>',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، احذف', cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#dc2626'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id, type: 'DELETE', dataType: 'json' })
                        .done(function (d) {
                            toastr.success(d.message);
                            setTimeout(function(){ window.location.href = '/admin/users'; }, 800);
                        })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشل الحذف'); });
                });
            });
        });
    </script>
@endsection
