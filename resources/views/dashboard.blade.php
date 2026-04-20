<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>حسابي — {{ $brand['system_ar'] }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-color: {{ $brand['primary_color'] }};
            --accent-color: {{ $brand['accent_color'] }};
            --accent-end: #FBBF24;
        }
        * { -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        /* ─── Compact Dark Header ──────────────────────── */
        .header-dark {
            position: relative;
            padding-bottom: 4rem;
            background:
                radial-gradient(700px circle at 10% 30%, rgba(249,115,22,.08) 0%, transparent 50%),
                radial-gradient(700px circle at 90% 70%, rgba(59,130,246,.06) 0%, transparent 50%),
                linear-gradient(135deg, var(--sidebar-color) 0%, #081830 70%, #040d1c 100%);
            color: white;
        }
        .header-decorations {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }
        /* Soft gradient fade at bottom to blend into content */
        .header-dark::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(180deg, transparent 0%, rgba(15,36,64,.4) 100%);
            pointer-events: none;
        }

        /* ─── Main Content (floating card) ─────────────── */
        .main-content {
            position: relative;
            z-index: 2;
            background: #f8fafc;
            border-radius: 32px 32px 0 0;
            margin-top: -48px;
            padding-top: 2.5rem;
            box-shadow:
                0 -20px 40px -20px rgba(15,23,42,.12),
                0 -1px 0 0 rgba(255,255,255,.06);
        }

        /* Subtle pull handle at top (decorative) */
        .main-content::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 9999px;
            opacity: .5;
        }

        /* Subtle pattern */
        .header-pattern::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.018) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        @keyframes fade-slide { from { opacity:0; transform: translateY(10px) } to { opacity:1; transform: translateY(0) } }
        .fade-slide { animation: fade-slide .5s ease-out both; }
        .delay-1 { animation-delay: .08s; }
        .delay-2 { animation-delay: .16s; }
        .delay-3 { animation-delay: .24s; }
        .delay-4 { animation-delay: .32s; }
        .delay-5 { animation-delay: .4s; }

        /* ─── System Card (Clean Minimal) ──────────────── */
        .sys-card {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            cursor: pointer;
            transition: all .25s cubic-bezier(.22,.61,.36,1);
            overflow: hidden;
        }

        /* Subtle colored accent bar at top (only visible on hover) */
        .sys-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0; left: 0;
            height: 3px;
            background: var(--sys-color);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform .3s ease;
            border-radius: 16px 16px 0 0;
        }

        .sys-card:hover {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--sys-color) 40%, #e2e8f0);
            box-shadow:
                0 12px 30px -12px color-mix(in srgb, var(--sys-color) 25%, transparent),
                0 4px 8px -4px rgba(15,23,42,.06);
        }
        .sys-card:hover::before { transform: scaleX(1); }

        /* Header row */
        .sys-card-header {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .sys-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: color-mix(in srgb, var(--sys-color) 10%, white);
            color: var(--sys-color);
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1.5px solid color-mix(in srgb, var(--sys-color) 20%, transparent);
            transition: all .25s ease;
        }
        .sys-card:hover .sys-icon {
            background: var(--sys-color);
            color: white;
            border-color: var(--sys-color);
            transform: scale(1.05) rotate(-3deg);
        }

        .sys-card-title {
            flex: 1;
            min-width: 0;
        }
        .sys-card-title h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sys-card-title p {
            font-size: .75rem;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* Meta info */
        .sys-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .sys-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .6rem;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: .7rem;
            font-weight: 500;
        }
        .sys-meta-chip svg { width: 12px; height: 12px; }

        /* CTA */
        .sys-card-cta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: .85rem;
            border-top: 1px solid #f1f5f9;
        }
        .sys-cta-text {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: #475569;
            font-weight: 600;
            font-size: .85rem;
            transition: all .2s ease;
        }
        .sys-card:hover .sys-cta-text {
            color: var(--sys-color);
            gap: .7rem;
        }
        .sys-cta-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--sys-color);
            opacity: .5;
            transition: opacity .2s ease, transform .2s ease;
        }
        .sys-card:hover .sys-cta-dot { opacity: 1; transform: scale(1.3); }

        /* Empty state card */
        .empty-card {
            grid-column: 1 / -1;
            background: white;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
        }

        /* Accent button */
        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-end) 100%);
            color: white; font-weight: 600;
            box-shadow: 0 8px 20px -6px color-mix(in srgb, var(--accent-color) 50%, transparent);
            transition: all .15s ease;
        }
        .btn-accent:hover { filter: brightness(1.08); transform: translateY(-1px); }

        .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px;
            border-radius: 10px;
            color: rgba(255,255,255,.65);
            transition: all .15s ease;
            position: relative;
            background: transparent;
            border: 0;
            cursor: pointer;
        }
        .icon-btn:hover { background: rgba(255,255,255,.08); color: white; }
        .icon-btn.has-badge::after {
            content: '';
            position: absolute;
            top: 10px; left: 10px;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #f97316;
            border: 2px solid var(--sidebar-color);
        }

        /* Profile dropdown */
        .profile-chip {
            display: flex; align-items: center; gap: .6rem;
            padding: .35rem .65rem .35rem .35rem;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            transition: all .15s ease;
            cursor: pointer;
        }
        .profile-chip:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.14); }
        .profile-chip-avatar {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-end));
        }
        .profile-chip-chevron {
            color: rgba(255,255,255,.5);
            transition: transform .2s;
        }
        .profile-chip[data-open="true"] .profile-chip-chevron { transform: rotate(180deg); }

        .profile-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 260px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 20px 50px -10px rgba(0,0,0,.3), 0 8px 15px -5px rgba(0,0,0,.15);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            opacity: 0; pointer-events: none;
            transform: translateY(-6px);
            transition: all .2s ease;
            z-index: 100;
        }
        .profile-menu[data-open="true"] {
            opacity: 1; pointer-events: auto; transform: translateY(0);
        }
        .profile-menu-header {
            padding: 1rem;
            background: linear-gradient(135deg, #f8fafc, white);
            border-bottom: 1px solid #e2e8f0;
        }
        .profile-menu-item {
            display: flex; align-items: center; gap: .65rem;
            padding: .7rem 1rem;
            font-size: .85rem;
            color: #334155;
            transition: background .1s;
            text-align: right;
            width: 100%;
            border: 0;
            background: transparent;
            cursor: pointer;
        }
        .profile-menu-item:hover { background: #f8fafc; color: #0f172a; }
        .profile-menu-item svg { color: #94a3b8; }
        .profile-menu-item.danger { color: #dc2626; }
        .profile-menu-item.danger:hover { background: #fef2f2; }
        .profile-menu-item.danger svg { color: #dc2626; }
        .profile-menu-divider { height: 1px; background: #e2e8f0; margin: .25rem 0; }

        .profile-mini {
            display: flex; align-items: center; gap: .75rem;
            padding: .75rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all .15s ease;
        }
        .profile-mini:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px -2px rgba(15,23,42,.06); }
    </style>
</head>
<body>

    <div class="header-dark header-pattern">
        <nav class="relative z-10 max-w-7xl mx-auto px-4 lg:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($brand['logo'])
                    <img src="{{ $brand['logo'] }}" alt="" class="h-10 w-auto">
                @else
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent-color), var(--accent-end))">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    </div>
                @endif
                <div class="leading-tight">
                    <p class="text-sm font-bold text-white">{{ $brand['system_ar'] }}</p>
                    <p class="text-[10px] text-white/50">{{ $brand['company_ar'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-accent hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm ml-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        لوحة التحكم
                    </a>
                @endif

                <button class="icon-btn has-badge" title="الإشعارات" id="notifications-btn">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>

                <a href="mailto:{{ $brand['support_email'] }}" class="icon-btn" title="المساعدة والدعم">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>

                <div class="relative">
                    <button type="button" class="profile-chip" id="profile-btn" data-open="false">
                        <div class="hidden sm:block leading-tight text-right">
                            <p class="text-xs font-semibold text-white truncate max-w-[120px]">{{ $user->full_name }}</p>
                            <p class="text-[10px] text-white/50">حسابي</p>
                        </div>
                        <div class="profile-chip-avatar">
                            {{ mb_substr($user->full_name, 0, 1) }}
                        </div>
                        <svg class="profile-chip-chevron w-3.5 h-3.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div class="profile-menu" id="profile-menu" data-open="false">
                        <div class="profile-menu-header">
                            <div class="flex items-center gap-3">
                                <div class="profile-chip-avatar" style="width:42px;height:42px;font-size:16px;border-radius:10px">
                                    {{ mb_substr($user->full_name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 text-sm truncate">{{ $user->full_name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate" dir="ltr">{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="py-1">
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="profile-menu-item">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    لوحة التحكم
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="profile-menu-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                الملف الشخصي
                            </a>
                            <a href="{{ route('profile.password') }}" class="profile-menu-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                تغيير كلمة المرور
                            </a>
                            <a href="{{ route('profile.sessions') }}" class="profile-menu-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                                جلساتي النشطة
                            </a>
                            <a href="{{ route('profile.activity') }}" class="profile-menu-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                سجل نشاطي
                            </a>
                        </div>
                        <div class="profile-menu-divider"></div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="profile-menu-item danger w-full">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-6 pt-4 pb-8">
            @php
                $hour = (int) now()->format('H');
                $greeting = match (true) {
                    $hour >= 5 && $hour < 12 => 'صباح الخير',
                    $hour >= 12 && $hour < 17 => 'مرحباً بك',
                    $hour >= 17 && $hour < 21 => 'مساء الخير',
                    default => 'أهلاً بك',
                };
            @endphp

            <div class="fade-slide flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm text-amber-300/80 font-medium mb-1">{{ $greeting }}،</p>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight">{{ $user->full_name }}</h1>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
                          style="background: rgba(249,115,22,.12); color: #fdba74; border: 1px solid rgba(249,115,22,.2);">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        {{ $linkedSystems->count() }} نظام متاح
                    </span>

                    @if ($user->last_login_at)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
                              style="background: rgba(16,185,129,.1); color: #6ee7b7; border: 1px solid rgba(52,211,153,.2);">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $user->last_login_at->diffForHumans() }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
                          style="background: rgba(255,255,255,.04); color: rgba(255,255,255,.7); border: 1px solid rgba(255,255,255,.08);">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
                            <span class="relative w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        </span>
                        جلسة مشفّرة
                    </span>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content">
     <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6">

        <section class="mb-10">
            <div class="flex items-end justify-between mb-6 flex-wrap gap-2">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">الأنظمة المتاحة لك</h2>
                    <p class="text-sm text-slate-500">اختر نظاماً للانتقال إليه مباشرة بتسجيل دخول واحد</p>
                </div>
                <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white border border-slate-200 text-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $linkedSystems->count() }} نظام
                </span>
            </div>

            @if ($linkedSystems->isEmpty())
                <div class="empty-card">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-slate-100 mb-5">
                        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <p class="text-slate-900 font-semibold text-lg mb-1">لا توجد أنظمة مرتبطة بحسابك</p>
                    <p class="text-sm text-slate-500 mb-5">تواصل مع مدير النظام لربط حسابك بالأنظمة المناسبة.</p>
                    <a href="mailto:{{ $brand['support_email'] }}" class="btn-accent inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        تواصل مع الدعم
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($linkedSystems as $i => $system)
                        @php $href = $system['launch_url'] ?? '#'; @endphp
                        <a href="{{ $href }}"
                           @if ($system['launch_url']) target="_blank" rel="noopener" @endif
                           class="sys-card fade-slide delay-{{ min($i + 1, 5) }}"
                           style="--sys-color: {{ $system['color'] }}">
                            <div class="sys-card-header">
                                <div class="sys-icon">{{ $system['initial'] }}</div>
                                <div class="sys-card-title">
                                    <h3>{{ $system['display_name'] }}</h3>
                                    <p dir="ltr">{{ $system['description'] ?: 'نظام مربوط بحسابك' }}</p>
                                </div>
                            </div>

                            <div class="sys-card-meta">
                                <span class="sys-meta-chip" dir="ltr">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    ID: {{ $system['external_user_id'] }}
                                </span>
                                @if ($system['last_accessed_at'])
                                    <span class="sys-meta-chip">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $system['last_accessed_at']->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            <div class="sys-card-cta">
                                <span class="sys-cta-text">
                                    فتح النظام
                                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </span>
                                <span class="sys-cta-dot"></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold text-slate-900">الملف الشخصي</h3>
                    <span class="text-xs text-slate-500">معلومات الحساب</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="profile-mini">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] text-slate-500">البريد الإلكتروني</p>
                            <p class="text-sm font-medium text-slate-900 truncate" dir="ltr">{{ $user->email }}</p>
                        </div>
                    </div>

                    @if ($user->phone)
                        <div class="profile-mini">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] text-slate-500">الهاتف</p>
                                <p class="text-sm font-medium text-slate-900" dir="ltr">{{ $user->phone }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="profile-mini">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-violet-50 text-violet-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] text-slate-500">آخر IP</p>
                            <p class="text-sm font-medium text-slate-900" dir="ltr">{{ $user->last_login_ip ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="profile-mini">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] text-slate-500">حالة الحساب</p>
                            <p class="text-sm font-medium text-emerald-600 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                نشط ومؤكّد
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-2">الدعم الفني</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">فريق متواجد على مدار الساعة لمساعدتك.</p>

                <div class="space-y-2.5">
                    @if ($brand['support_phone'])
                        <a href="tel:{{ $brand['support_phone'] }}" class="profile-mini w-full">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] text-slate-500">اتصل الآن</p>
                                <p class="text-sm font-medium text-slate-900" dir="ltr">{{ $brand['support_phone'] }}</p>
                            </div>
                        </a>
                    @endif

                    @if ($brand['support_email'])
                        <a href="mailto:{{ $brand['support_email'] }}" class="profile-mini w-full">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] text-slate-500">راسلنا</p>
                                <p class="text-sm font-medium text-slate-900 truncate" dir="ltr">{{ $brand['support_email'] }}</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <footer class="mt-10 pt-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <span>&copy; {{ date('Y') }} {{ $brand['company_ar'] }} — جميع الحقوق محفوظة</span>
            <span>الإصدار 1.0</span>
        </footer>
     </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $btn = $('#profile-btn');
            const $menu = $('#profile-menu');

            $btn.on('click', function (e) {
                e.stopPropagation();
                const isOpen = $menu.attr('data-open') === 'true';
                $menu.attr('data-open', isOpen ? 'false' : 'true');
                $btn.attr('data-open', isOpen ? 'false' : 'true');
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#profile-btn, #profile-menu').length) {
                    $menu.attr('data-open', 'false');
                    $btn.attr('data-open', 'false');
                }
            });

            $('#notifications-btn').on('click', function () {
                toastr.info('لا توجد إشعارات جديدة');
            });
        });
    </script>
</body>
</html>
