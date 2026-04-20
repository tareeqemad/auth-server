<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — {{ $brand['system_ar'] }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-color: {{ $brand['primary_color'] }};
            --accent-color: {{ $brand['accent_color'] }};
            --accent-end: #FBBF24;
            --sidebar-w: 16rem;
            --navbar-h: 64px;
            --accent-h: 3px;
            --breadcrumb-h: 52px;
        }
        * { -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }

        /* ── Accent strip (full width at top) ──────────── */
        .accent-strip {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--accent-h);
            z-index: 60;
            background: linear-gradient(90deg, var(--accent-color) 0%, var(--accent-end) 50%, var(--accent-color) 100%);
            background-size: 200% 100%;
            animation: strip-flow 8s linear infinite;
        }
        @keyframes strip-flow {
            0% { background-position: 0% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Sidebar (DARK, full height, right in RTL) ── */
        .admin-sidebar {
            position: fixed;
            top: var(--accent-h);
            right: 0;
            width: var(--sidebar-w);
            height: calc(100vh - var(--accent-h));
            z-index: 50;
            background: linear-gradient(180deg, var(--sidebar-color) 0%, #081830 100%);
            border-left: 1px solid rgba(255,255,255,.06);
            color: rgba(255,255,255,.85);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform .3s cubic-bezier(.22,.61,.36,1);
            box-shadow: -20px 0 40px -10px rgba(0,0,0,.3);
        }
        .admin-sidebar.open { transform: translateX(0); }

        @media (min-width: 1024px) {
            .admin-sidebar {
                transform: translateX(0);
                box-shadow: none;
            }
            body.sidebar-hidden .admin-sidebar { transform: translateX(100%); }
        }

        /* Sidebar header (brand area) */
        .sidebar-header {
            height: var(--navbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: .65rem;
            color: white;
            flex: 1; min-width: 0;
        }
        .sidebar-brand-logo {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            padding: 5px;
        }
        .sidebar-brand-text { min-width: 0; line-height: 1.2; }
        .sidebar-brand-text p:first-child {
            font-weight: 700;
            color: white;
            font-size: .9rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-brand-text p:last-child {
            font-size: 10px;
            color: rgba(255,255,255,.5);
        }

        /* Backdrop for mobile */
        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }
        #sidebar-backdrop.open { opacity: 1; pointer-events: auto; }
        @media (min-width: 1024px) { #sidebar-backdrop { display: none; } }

        /* Hamburger */
        .hamburger-icon { display: flex; flex-direction: column; gap: 4px; width: 20px; }
        .hamburger-icon span {
            display: block;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            transition: all .3s ease;
        }
        .hamburger-icon span:nth-child(1) { width: 100%; }
        .hamburger-icon span:nth-child(2) { width: 70%; }
        .hamburger-icon span:nth-child(3) { width: 85%; }
        #sidebar-toggle:hover .hamburger-icon span { width: 100%; }

        /* Nav links */
        .nav-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .7rem 1rem;
            border-radius: .75rem;
            color: rgba(255,255,255,.6);
            font-weight: 500;
            font-size: .875rem;
            transition: all .15s ease;
            position: relative;
        }
        .nav-link:hover:not(.disabled) { background: rgba(255,255,255,.05); color: white; }
        .nav-link.active {
            background: linear-gradient(135deg, color-mix(in srgb, var(--accent-color) 18%, transparent) 0%, color-mix(in srgb, var(--accent-color) 6%, transparent) 100%);
            color: var(--accent-color);
            font-weight: 600;
        }
        .nav-link.active::before {
            content:'';
            position: absolute;
            right: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: var(--accent-color);
            border-radius: 3px 0 0 3px;
            box-shadow: 0 0 10px var(--accent-color);
        }
        .nav-link.disabled { opacity: .35; cursor: not-allowed; }
        .sidebar-heading {
            font-size: .65rem;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            font-weight: 700;
            letter-spacing: .15em;
            padding: 0 1rem;
            margin: 1.25rem 0 .5rem;
        }

        /* ── Main wrapper ──────────────────────────────── */
        .main-wrapper {
            padding-top: var(--accent-h);
            min-height: 100vh;
            transition: margin-right .3s cubic-bezier(.22,.61,.36,1);
        }
        @media (min-width: 1024px) {
            .main-wrapper { margin-right: var(--sidebar-w); }
            body.sidebar-hidden .main-wrapper { margin-right: 0; }
        }

        /* ── Top Navbar (in main area only) ────────────── */
        .top-navbar {
            position: sticky;
            top: var(--accent-h);
            z-index: 40;
            height: var(--navbar-h);
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px) saturate(140%);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            box-shadow: 0 2px 8px -4px rgba(15,23,42,.05);
        }
        @media (min-width: 1024px) { .top-navbar { padding: 0 1.5rem; } }

        /* ── Breadcrumb Bar ─────────────────────────────── */
        .breadcrumb-bar {
            position: sticky;
            top: calc(var(--navbar-h) + var(--accent-h));
            z-index: 30;
            height: var(--breadcrumb-h);
            background: rgba(248,250,252,.9);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }
        @media (min-width: 1024px) { .breadcrumb-bar { padding: 0 2rem; } }

        .bc-link {
            color: #64748b; font-size: .8rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: .4rem;
            transition: color .15s ease;
        }
        .bc-link:hover { color: var(--accent-color); }
        .bc-sep { color: #cbd5e1; margin: 0 .5rem; font-weight: 400; }
        .bc-current { color: #0f172a; font-size: .8rem; font-weight: 600; }

        /* ── Buttons ──────────────────────────────────── */
        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-end) 100%);
            color: white; font-weight: 600;
            box-shadow: 0 8px 20px -6px color-mix(in srgb, var(--accent-color) 45%, transparent);
            transition: all .15s ease;
        }
        .btn-accent:hover:not(:disabled) { filter: brightness(1.05); transform: translateY(-1px); }
        .btn-accent:active:not(:disabled) { transform: translateY(0) scale(.99); }
        .btn-accent:disabled { opacity: .6; cursor: not-allowed; }

        .btn-ghost {
            background: white; border: 1px solid #e2e8f0; color: #475569;
            font-weight: 500; transition: all .15s ease;
        }
        .btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

        /* ── Inputs ───────────────────────────────────── */
        .input-glass {
            background: white; border: 1.5px solid #e2e8f0; color: #0f172a;
            transition: all .15s ease;
        }
        .input-glass::placeholder { color: #94a3b8; }
        .input-glass:hover:not(:focus) { border-color: #cbd5e1; }
        .input-glass:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-color) 15%, transparent);
        }
        .input-glass.error { border-color: #f87171; box-shadow: 0 0 0 3px rgba(248,113,113,.12); }
        .input-glass[readonly] { background: #f8fafc; color: #64748b; }
        textarea.input-glass { resize: vertical; }

        /* ── Cards ────────────────────────────────────── */
        .card-glass {
            background: white; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(15,23,42,.04);
        }

        /* ── Table ────────────────────────────────────── */
        .table-glass { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-glass thead th {
            background: #f8fafc; color: #64748b;
            font-size: .7rem; text-transform: uppercase;
            font-weight: 600; letter-spacing: .08em;
            padding: .85rem 1.5rem; text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-glass tbody td {
            padding: .9rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: .875rem; color: #334155;
        }
        .table-glass tbody tr:hover { background: #f8fafc; }
        .table-glass tbody tr:last-child td { border-bottom: none; }

        /* ── Badges ───────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .25rem .7rem;
            border-radius: 9999px;
            font-size: .7rem; font-weight: 600;
        }
        .badge-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── Icon buttons ─────────────────────────────── */
        .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px;
            border-radius: 10px;
            color: #64748b;
            transition: all .15s ease;
            position: relative;
        }
        .icon-btn:hover { background: #f1f5f9; color: #0f172a; }
        .icon-btn.danger:hover { background: #fef2f2; color: #b91c1c; }
        .icon-btn.warning:hover { background: #fffbeb; color: #b45309; }
        .icon-btn.has-badge::after {
            content: '';
            position: absolute;
            top: 8px; left: 8px;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #ef4444;
            border: 2px solid white;
        }

        /* User chip/avatar */
        .user-chip {
            display: flex; align-items: center; gap: .6rem;
            padding: .35rem .6rem .35rem .35rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all .15s ease;
        }
        .user-chip:hover { background: white; border-color: #cbd5e1; box-shadow: 0 2px 6px -2px rgba(15,23,42,.08); }
        .user-avatar {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-end));
            flex-shrink: 0;
        }
        .user-chip-desktop { display: flex; }
        .user-avatar-mobile { display: none; }
        @media (max-width: 767px) {
            .user-chip-desktop { display: none; }
            .user-avatar-mobile { display: inline-flex; }
        }

        /* Close button (mobile sidebar) */
        .sidebar-close-btn {
            display: none;
            width: 36px; height: 36px;
            border-radius: 10px;
            align-items: center; justify-content: center;
            color: rgba(255,255,255,.5);
            background: transparent;
            transition: all .15s ease;
        }
        .sidebar-close-btn:hover { background: rgba(255,255,255,.08); color: white; }
        @media (max-width: 1023px) {
            .sidebar-close-btn { display: inline-flex; }
        }

        /* Animations */
        @keyframes fade-in { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
        .fade-in { animation: fade-in .4s ease-out both; }
        .spinner { border: 2px solid rgba(255,255,255,.3); border-top-color: white; }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 2px solid #f8fafc; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>

    <div class="accent-strip"></div>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-logo">
                    @if ($brand['logo'])
                        <img src="{{ $brand['logo'] }}" alt="" class="max-w-full max-h-full object-contain">
                    @else
                        <svg class="w-5 h-5 text-amber-300" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    @endif
                </div>
                <div class="sidebar-brand-text">
                    <p>{{ $brand['system_ar'] }}</p>
                    <p>لوحة التحكم</p>
                </div>
            </a>

            <button id="sidebar-close" class="sidebar-close-btn" aria-label="إغلاق">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>الرئيسية</span>
            </a>

            <p class="sidebar-heading">الإدارة</p>

            <a href="{{ route('admin.applications.index') }}" class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>الأنظمة</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                <span>المستخدمون</span>
            </a>

            <p class="sidebar-heading">المراقبة</p>

            <a href="{{ route('admin.audit_logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>سجل الأحداث</span>
            </a>

            <a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                <span>الجلسات النشطة</span>
            </a>

            <p class="sidebar-heading">الإعدادات</p>

            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>الإعدادات العامة</span>
            </a>

            <a href="{{ route('admin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>المدراء والصلاحيات</span>
            </a>
        </nav>
    </aside>

    <div id="sidebar-backdrop"></div>

    <div class="main-wrapper">
        <nav class="top-navbar">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <button id="sidebar-toggle" class="icon-btn shrink-0" aria-label="تبديل القائمة">
                    <span class="hamburger-icon"><span></span><span></span><span></span></span>
                </button>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <button class="icon-btn has-badge" title="الإشعارات">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="icon-btn" title="حسابي الشخصي">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <div class="user-chip user-chip-desktop">
                    <div class="leading-tight text-right">
                        <p class="font-semibold text-slate-900 text-xs truncate max-w-[120px]">{{ auth()->user()->full_name }}</p>
                        <p class="text-[10px] text-slate-500">
                            @foreach(auth()->user()->getRoleNames() as $r)
                                {{ $r }}
                            @endforeach
                        </p>
                    </div>
                    <div class="user-avatar">
                        {{ mb_substr(auth()->user()->full_name, 0, 1) }}
                    </div>
                </div>

                <div class="user-avatar user-avatar-mobile">
                    {{ mb_substr(auth()->user()->full_name, 0, 1) }}
                </div>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="icon-btn danger" title="تسجيل الخروج">
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </nav>

        <div class="breadcrumb-bar">
            <div class="flex items-center flex-wrap gap-y-1 min-w-0">
                @hasSection('breadcrumbs')
                    <a href="{{ route('admin.dashboard') }}" class="bc-link">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>
                    <span class="bc-sep">‹</span>
                    @yield('breadcrumbs')
                @else
                    <a href="{{ route('admin.dashboard') }}" class="bc-link">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        الرئيسية
                    </a>
                @endif
            </div>

            @hasSection('breadcrumb_actions')
                <div class="flex items-center gap-2 shrink-0">
                    @yield('breadcrumb_actions')
                </div>
            @endif
        </div>

        <main class="p-4 sm:p-6 lg:p-8">
            <div class="fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $sidebar = $('.admin-sidebar');
            const $backdrop = $('#sidebar-backdrop');
            const $body = $('body');

            const isDesktop = () => window.innerWidth >= 1024;

            function openMobileSidebar() {
                $sidebar.addClass('open');
                $backdrop.addClass('open');
                $body.css('overflow', 'hidden');
            }
            function closeMobileSidebar() {
                $sidebar.removeClass('open');
                $backdrop.removeClass('open');
                $body.css('overflow', '');
            }
            function toggleDesktopSidebar() {
                $body.toggleClass('sidebar-hidden');
                try { localStorage.setItem('sidebar-hidden', $body.hasClass('sidebar-hidden') ? '1' : '0'); } catch (e) {}
            }

            if (localStorage.getItem('sidebar-hidden') === '1') $body.addClass('sidebar-hidden');

            $('#sidebar-toggle').on('click', function () {
                if (isDesktop()) toggleDesktopSidebar();
                else openMobileSidebar();
            });

            $('#sidebar-close, #sidebar-backdrop').on('click', closeMobileSidebar);

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $sidebar.hasClass('open')) closeMobileSidebar();
            });

            $sidebar.find('.nav-link').on('click', function () {
                if (!isDesktop() && !$(this).hasClass('disabled')) setTimeout(closeMobileSidebar, 150);
            });

            $(window).on('resize', function () {
                if (isDesktop() && $sidebar.hasClass('open')) closeMobileSidebar();
            });
        });
    </script>
</body>
</html>
