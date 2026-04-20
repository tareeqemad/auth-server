<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'حسابي') — {{ $brand['system_ar'] }}</title>

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

        .header-dark {
            position: relative;
            padding-bottom: 4rem;
            background:
                radial-gradient(600px circle at 10% 30%, rgba(249,115,22,.08) 0%, transparent 50%),
                radial-gradient(600px circle at 90% 70%, rgba(59,130,246,.06) 0%, transparent 50%),
                linear-gradient(135deg, var(--sidebar-color) 0%, #081830 70%, #040d1c 100%);
            color: white;
            overflow: hidden;
        }
        .header-dark::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(180deg, transparent, rgba(15,36,64,.4));
            pointer-events: none;
        }

        .main-content {
            position: relative;
            z-index: 2;
            background: #f8fafc;
            border-radius: 32px 32px 0 0;
            margin-top: -48px;
            padding-top: 2.5rem;
            box-shadow: 0 -20px 40px -20px rgba(15,23,42,.12);
        }
        .main-content::before {
            content: '';
            position: absolute;
            top: 10px; left: 50%; transform: translateX(-50%);
            width: 40px; height: 4px;
            background: #cbd5e1; border-radius: 9999px;
            opacity: .5;
        }

        .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px;
            border-radius: 10px;
            color: rgba(255,255,255,.65);
            transition: all .15s ease;
            background: transparent;
            border: 0; cursor: pointer;
        }
        .icon-btn:hover { background: rgba(255,255,255,.08); color: white; }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-end));
            color: white; font-weight: 600;
            box-shadow: 0 8px 20px -6px color-mix(in srgb, var(--accent-color) 50%, transparent);
            transition: all .15s ease;
            border: 0;
            cursor: pointer;
        }
        .btn-accent:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); }
        .btn-accent:disabled { opacity: .6; cursor: not-allowed; }

        .btn-ghost {
            background: white; border: 1px solid #e2e8f0; color: #475569;
            font-weight: 500; transition: all .15s ease; cursor: pointer;
        }
        .btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

        .input-clean {
            background: white;
            border: 1.5px solid #e2e8f0;
            color: #0f172a;
            transition: all .15s ease;
            width: 100%;
            padding: .7rem 1rem;
            border-radius: .75rem;
            font-size: .875rem;
        }
        .input-clean::placeholder { color: #94a3b8; }
        .input-clean:hover:not(:focus) { border-color: #cbd5e1; }
        .input-clean:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-color) 15%, transparent);
        }
        .input-clean.error { border-color: #f87171; box-shadow: 0 0 0 3px rgba(248,113,113,.12); }
        .input-clean[readonly] { background: #f8fafc; color: #64748b; cursor: not-allowed; }

        /* Tabs */
        .profile-tabs {
            display: flex;
            gap: .25rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: .3rem;
            overflow-x: auto;
        }
        .profile-tab {
            flex: 1;
            min-width: max-content;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .65rem 1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 500;
            color: #64748b;
            transition: all .15s ease;
            white-space: nowrap;
        }
        .profile-tab:hover { color: #0f172a; background: #f8fafc; }
        .profile-tab.active {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-end));
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px -4px color-mix(in srgb, var(--accent-color) 45%, transparent);
        }

        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            transition: all .15s ease;
        }

        .spinner { border: 2px solid rgba(255,255,255,.3); border-top-color: white; }

        @keyframes fade-in { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
        .fade-in { animation: fade-in .35s ease-out both; }

        .badge {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .25rem .7rem;
            border-radius: 9999px;
            font-size: .7rem;
            font-weight: 600;
        }
        .badge-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    </style>
</head>
<body>

    <div class="header-dark">
        <nav class="relative z-10 max-w-5xl mx-auto px-4 lg:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
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
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="icon-btn" title="العودة للرئيسية">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="تسجيل الخروج">
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </nav>

        <div class="relative z-10 max-w-5xl mx-auto px-4 lg:px-6 pt-6 pb-8">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl sm:text-3xl shrink-0"
                     style="background: linear-gradient(135deg, var(--accent-color), var(--accent-end)); box-shadow: 0 12px 30px -8px color-mix(in srgb, var(--accent-color) 60%, transparent);">
                    {{ mb_substr($user->full_name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-amber-300/70 font-medium mb-1">حسابي</p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">{{ $user->full_name }}</h1>
                    <p class="text-sm text-white/50 truncate" dir="ltr">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="max-w-5xl mx-auto px-4 lg:px-6 py-6">

            <nav class="profile-tabs mb-6">
                <a href="{{ route('profile.edit') }}" class="profile-tab {{ request()->routeIs('profile.edit') || request()->routeIs('profile.update') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>بياناتي</span>
                </a>
                <a href="{{ route('profile.password') }}" class="profile-tab {{ request()->routeIs('profile.password*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>كلمة المرور</span>
                </a>
                <a href="{{ route('profile.security') }}" class="profile-tab {{ request()->routeIs('profile.security*') || request()->routeIs('profile.2fa*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>الأمان</span>
                </a>
                <a href="{{ route('profile.sessions') }}" class="profile-tab {{ request()->routeIs('profile.sessions*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                    <span>جلساتي</span>
                </a>
                <a href="{{ route('profile.activity') }}" class="profile-tab {{ request()->routeIs('profile.activity') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>سجل نشاطي</span>
                </a>
            </nav>

            @if (session('status'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm fade-in">
                    {{ session('status') }}
                </div>
            @endif

            <div class="fade-in">
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
