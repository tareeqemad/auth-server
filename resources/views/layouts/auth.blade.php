<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $brand['system_ar']) — {{ $brand['company_ar'] }}</title>

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
            background:
                radial-gradient(1000px circle at 20% 15%, rgba(249,115,22,.10) 0%, transparent 50%),
                radial-gradient(1000px circle at 80% 85%, rgba(59,130,246,.08) 0%, transparent 55%),
                linear-gradient(135deg, var(--sidebar-color) 0%, #081830 60%, #040d1c 100%);
            background-attachment: fixed;
        }
        .font-display { font-family: 'Amiri', 'Tajawal', serif; }

        /* Animations */
        @keyframes float-y { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }
        @keyframes pulse-glow { 0%,100% { opacity:.35; transform: scale(1) } 50% { opacity:.6; transform: scale(1.1) } }
        @keyframes rotate-slow { from { transform: rotate(0) } to { transform: rotate(360deg) } }
        @keyframes fade-slide-up { from { opacity:0; transform: translateY(16px) } to { opacity:1; transform: translateY(0) } }
        @keyframes underline-grow { from { width: 0 } to { width: 3rem } }
        @keyframes dot-pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 currentColor; }
            50% { transform: scale(1.15); box-shadow: 0 0 0 6px transparent; }
        }
        @keyframes drift-1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -20px) scale(1.1); }
            66% { transform: translate(-20px, 15px) scale(0.95); }
        }
        @keyframes drift-2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-40px, 30px) scale(1.08); }
        }
        @keyframes twinkle {
            0%, 100% { opacity: .15; transform: scale(1); }
            50% { opacity: .8; transform: scale(1.4); }
        }

        .float-anim { animation: float-y 4s ease-in-out infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .rotate-slow { animation: rotate-slow 90s linear infinite; }
        .fade-slide-up { animation: fade-slide-up .6s cubic-bezier(.22,.61,.36,1) both; }
        .delay-100 { animation-delay: .1s; }
        .delay-200 { animation-delay: .2s; }
        .delay-300 { animation-delay: .3s; }
        .delay-400 { animation-delay: .4s; }

        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            pointer-events: none;
        }
        .orb-1 { animation: drift-1 18s ease-in-out infinite; }
        .orb-2 { animation: drift-2 22s ease-in-out infinite; }

        .twinkle {
            position: absolute;
            border-radius: 9999px;
            background: white;
            pointer-events: none;
            animation: twinkle 4s ease-in-out infinite;
        }

        /* Buttons */
        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-end) 100%);
            box-shadow:
                0 14px 40px -10px color-mix(in srgb, var(--accent-color) 55%, transparent),
                inset 0 1px 0 rgba(255,255,255,.25);
            transition: all .2s cubic-bezier(.22,.61,.36,1);
            position: relative;
            overflow: hidden;
        }
        .btn-accent::before {
            content:'';
            position: absolute; inset:0;
            background: linear-gradient(100deg, transparent 30%, rgba(255,255,255,.3) 50%, transparent 70%);
            background-size: 200% 100%;
            transform: translateX(100%);
            transition: transform .7s ease;
        }
        .btn-accent:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); box-shadow: 0 18px 50px -10px color-mix(in srgb, var(--accent-color) 65%, transparent), inset 0 1px 0 rgba(255,255,255,.3); }
        .btn-accent:hover:not(:disabled)::before { transform: translateX(-100%); }
        .btn-accent:active:not(:disabled) { transform: translateY(0) scale(.99); }
        .btn-accent:disabled { opacity: .65; cursor: not-allowed; }

        /* Inputs — darker, smart focus glow */
        .input-glass {
            background: rgba(8,24,48,0.6);
            border: 1.5px solid rgba(255,255,255,0.08);
            color: white;
            transition: all .2s ease;
        }
        .input-glass::placeholder { color: rgba(255,255,255,0.25); }
        .input-glass:hover:not(:focus):not([readonly]) { background: rgba(8,24,48,0.75); border-color: rgba(255,255,255,.14); }
        .input-glass:focus {
            outline: none;
            background: rgba(8,24,48,0.9);
            border-color: var(--accent-color);
            box-shadow:
                0 0 0 4px color-mix(in srgb, var(--accent-color) 14%, transparent),
                0 0 25px -6px color-mix(in srgb, var(--accent-color) 50%, transparent),
                inset 0 1px 0 rgba(255,255,255,.03);
        }
        .input-glass.error {
            border-color: rgba(248,113,113,.7);
            box-shadow: 0 0 0 4px rgba(248,113,113,.15);
            background: rgba(127,29,29,.15);
        }

        /* Glass Card */
        .card-glass {
            background: linear-gradient(160deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.03) 100%);
            backdrop-filter: blur(24px) saturate(140%);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow:
                0 30px 60px -15px rgba(0,0,0,.5),
                inset 0 1px 0 rgba(255,255,255,.06);
        }

        .text-gradient {
            background: linear-gradient(135deg, #fff 0%, #cbd5e1 55%, #94a3b8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-circles > div {
            border: 1px solid rgba(255,255,255,.07);
            position: absolute;
            border-radius: 9999px;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .spinner { border: 2px solid rgba(255,255,255,.3); border-top-color: white; }

        /* Systems dots */
        .sys-dot {
            width: 14px; height: 14px;
            border-radius: 9999px;
            box-shadow: 0 0 12px currentColor, inset 0 1px 0 rgba(255,255,255,.3);
            animation: dot-pulse 3s ease-in-out infinite;
            transition: transform .2s ease;
            cursor: default;
        }
        .sys-dot:hover { transform: scale(1.35); }

        /* Status badge — smaller */
        .status-badge {
            background: rgba(16,185,129,.08);
            border: 1px solid rgba(52,211,153,.18);
        }

        /* Inline stats */
        .stat-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .3rem .65rem;
            border-radius: 9999px;
            background: rgba(255,255,255,.035);
            border: 1px solid rgba(255,255,255,.06);
            font-size: 11px;
            color: rgba(255,255,255,.55);
            transition: all .2s ease;
        }
        .stat-chip:hover {
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.12);
            color: rgba(255,255,255,.9);
        }
    </style>
</head>
<body class="min-h-screen antialiased text-white">
    <div class="min-h-screen flex">

        <aside class="hidden lg:flex lg:w-[52%] xl:w-[55%] flex-col items-center justify-center p-10 xl:p-14 relative overflow-hidden bg-grid-overlay">

            <div class="hero-circles absolute inset-0 pointer-events-none rotate-slow opacity-[.7]">
                @for ($i = 1; $i <= 7; $i++)
                    <div style="width: {{ 260 + $i * 110 }}px; height: {{ 260 + $i * 110 }}px; opacity: {{ 0.08 - $i * 0.008 }}"></div>
                @endfor
            </div>

            <div class="orb orb-1" style="width: 320px; height: 320px; top: -80px; left: -60px; background: radial-gradient(circle, rgba(249,115,22,.22) 0%, transparent 65%);"></div>
            <div class="orb orb-2" style="width: 260px; height: 260px; bottom: -40px; right: 10%; background: radial-gradient(circle, rgba(59,130,246,.18) 0%, transparent 65%);"></div>
            <div class="orb orb-1" style="width: 200px; height: 200px; top: 35%; right: -40px; background: radial-gradient(circle, rgba(251,191,36,.15) 0%, transparent 65%); animation-delay: -7s;"></div>

            <span class="twinkle" style="width: 3px; height: 3px; top: 12%; left: 20%; animation-delay: 0s;"></span>
            <span class="twinkle" style="width: 2px; height: 2px; top: 25%; left: 70%; animation-delay: 1.2s;"></span>
            <span class="twinkle" style="width: 4px; height: 4px; top: 18%; left: 50%; animation-delay: 2.5s;"></span>
            <span class="twinkle" style="width: 2px; height: 2px; bottom: 30%; left: 15%; animation-delay: 0.8s;"></span>
            <span class="twinkle" style="width: 3px; height: 3px; bottom: 18%; left: 65%; animation-delay: 3s;"></span>
            <span class="twinkle" style="width: 2px; height: 2px; bottom: 45%; left: 80%; animation-delay: 1.8s;"></span>
            <span class="twinkle" style="width: 3px; height: 3px; top: 60%; left: 8%; animation-delay: 2.2s;"></span>
            <span class="twinkle" style="width: 2px; height: 2px; top: 40%; left: 90%; animation-delay: 0.5s;"></span>

            <div class="absolute top-8 right-10 xl:right-14 fade-slide-up z-10">
                <div class="status-badge status-badge-pulse inline-flex items-center gap-2 px-3 py-1.5 rounded-full">
                    <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[11px] font-semibold text-emerald-300/90">النظام متاح</span>
                    <span class="relative flex w-1.5 h-1.5">
                        <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
                        <span class="relative w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    </span>
                </div>
            </div>

            <div class="relative z-10 text-center max-w-xl fade-slide-up delay-100">
                <div class="relative inline-block mb-7">
                    <div class="absolute inset-0 rounded-full blur-2xl pulse-glow"
                         style="background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%); transform: scale(1.6);"></div>

                    @if ($brand['logo'])
                        <img src="{{ $brand['logo'] }}" alt="{{ $brand['company_ar'] }}"
                             class="relative w-24 h-24 object-contain float-anim drop-shadow-2xl">
                    @else
                        <div class="relative w-24 h-24 rounded-3xl flex items-center justify-center float-anim"
                             style="background: linear-gradient(145deg, rgba(255,255,255,.1), rgba(255,255,255,.04)); border: 1px solid rgba(255,255,255,.14);">
                            <svg class="w-12 h-12 text-amber-300" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <p class="text-[10px] font-bold tracking-[.35em] text-amber-400/70 uppercase mb-3">GEDCO · Single Sign-On</p>

                <h1 class="text-5xl xl:text-6xl font-bold text-gradient mb-5 leading-[1.1]">
                    {{ $brand['system_ar'] }}
                </h1>

                <p class="text-xl text-white/85 mb-4 leading-relaxed">
                    بوابة الدخول الموحّدة لجميع أنظمة الشركة
                </p>

                <div class="inline-flex items-center gap-2 mb-8">
                    <span class="h-px w-8" style="background: linear-gradient(90deg, transparent, var(--accent-color))"></span>
                    <span class="font-display text-base text-amber-300/80 italic">{{ $brand['tagline'] }}</span>
                    <span class="h-px w-8" style="background: linear-gradient(90deg, var(--accent-color), transparent)"></span>
                </div>

                @if (! empty($brand['applications']))
                    <div class="fade-slide-up delay-300">
                        <p class="text-xs text-white/40 mb-4">
                            <span class="font-semibold text-white/70">{{ count($brand['applications']) }}</span>
                            أنظمة متصلة بتسجيل دخول واحد
                        </p>

                        <div class="flex items-center justify-center gap-3 mb-5">
                            @foreach ($brand['applications'] as $idx => $app)
                                <button type="button" data-sys-idx="{{ $idx }}"
                                        class="sys-preview {{ $idx === 0 ? 'active' : '' }}"
                                        style="--sys-color: {{ $app['color'] }}"
                                        title="{{ $app['name'] }}">
                                    {{ mb_substr($app['name'], 0, 1) }}
                                </button>
                            @endforeach
                        </div>

                        <p data-sys-name class="text-center text-sm font-medium text-white/75 h-5 transition-opacity">
                            {{ $brand['applications'][0]['name'] ?? '' }}
                        </p>

                        <div class="flex items-center justify-center gap-1.5 mt-3">
                            @foreach ($brand['applications'] as $idx => $app)
                                <button type="button" data-sys-dot="{{ $idx }}"
                                        class="sys-pag-dot {{ $idx === 0 ? 'active' : '' }}"
                                        aria-label="{{ $app['name'] }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex items-center gap-2 fade-slide-up delay-400">
                <span class="stat-chip">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    حماية كاملة
                </span>
                <span class="stat-chip">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    سريع وسلس
                </span>
                <span class="stat-chip">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    دعم 24/7
                </span>
            </div>

            <div class="absolute bottom-4 right-10 xl:right-14 text-[10px] text-white/20 tracking-wider">
                v1.0 · &copy; {{ date('Y') }} GEDCO
            </div>
        </aside>

        <script>
            (function() {
                const systems = @json($brand['applications'] ?? []);
                if (!systems.length) return;

                let active = 0, interval;

                function setActive(i) {
                    active = i;
                    document.querySelectorAll('.sys-preview, .sys-pag-dot').forEach(el => el.classList.remove('active'));
                    document.querySelector(`[data-sys-idx="${i}"]`)?.classList.add('active');
                    document.querySelector(`[data-sys-dot="${i}"]`)?.classList.add('active');
                    const nameEl = document.querySelector('[data-sys-name]');
                    if (nameEl) {
                        nameEl.style.opacity = '0';
                        setTimeout(() => {
                            nameEl.textContent = systems[i].name;
                            nameEl.style.opacity = '1';
                        }, 150);
                    }
                }

                function startCycle() {
                    interval = setInterval(() => setActive((active + 1) % systems.length), 3200);
                }

                document.addEventListener('click', (e) => {
                    const el = e.target.closest('[data-sys-idx], [data-sys-dot]');
                    if (!el) return;
                    const i = parseInt(el.getAttribute('data-sys-idx') ?? el.getAttribute('data-sys-dot'));
                    setActive(i);
                    clearInterval(interval);
                    startCycle();
                });

                startCycle();
            })();
        </script>

        <main class="flex-1 flex items-center justify-center p-4 lg:p-8 relative">
            <div class="absolute top-5 left-5 right-5 lg:hidden flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    @if ($brand['logo'])
                        <img src="{{ $brand['logo'] }}" alt="" class="w-9 h-9 object-contain">
                    @endif
                    <div>
                        <p class="text-sm font-bold text-white leading-tight">{{ $brand['system_ar'] }}</p>
                        <p class="text-[10px] text-white/50 leading-tight">{{ $brand['company_ar'] }}</p>
                    </div>
                </div>
                <span class="relative flex w-1.5 h-1.5">
                    <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
                    <span class="relative w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                </span>
            </div>

            <div class="w-full max-w-md fade-slide-up delay-200">
                <div class="card-glass rounded-3xl p-7 lg:p-9 mt-14 lg:mt-0">
                    <div class="mb-6">
                        @php
                            $hour = (int) now()->format('H');
                            $greeting = match (true) {
                                $hour >= 5 && $hour < 12 => 'صباح الخير',
                                $hour >= 12 && $hour < 17 => 'مرحباً بك',
                                $hour >= 17 && $hour < 21 => 'مساء الخير',
                                default => 'أهلاً بك',
                            };
                        @endphp
                        <p class="text-sm font-medium text-amber-400/80 mb-2">{{ $greeting }}،</p>
                        <h2 class="text-2xl lg:text-[26px] font-bold text-white leading-tight">@yield('heading')</h2>
                        @hasSection('subheading')
                            <p class="text-sm text-white/50 mt-2">@yield('subheading')</p>
                        @endif
                    </div>

                    @if (session('status'))
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl mb-5 fade-slide-up"
                             style="background: rgba(16,185,129,.1); border: 1px solid rgba(52,211,153,.25);">
                            <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-sm text-emerald-300">{{ session('status') }}</span>
                        </div>
                    @endif

                    @yield('content')

                    <div class="mt-5 pt-4 relative" style="border-top: 1px solid rgba(255,255,255,0.06)">
                        <div class="flex items-center justify-center gap-3 text-[10px] text-white/30">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-400/70" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                مشفّر
                            </span>
                            @if ($brand['support_phone'])
                                <span class="w-px h-3 bg-white/10"></span>
                                <a href="tel:{{ $brand['support_phone'] }}" dir="ltr" class="hover:text-white/60 transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>
                                    {{ $brand['support_phone'] }}
                                </a>
                            @endif
                            @if ($brand['support_email'])
                                <span class="w-px h-3 bg-white/10"></span>
                                <a href="mailto:{{ $brand['support_email'] }}" dir="ltr" class="hover:text-white/60 transition">
                                    {{ $brand['support_email'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <p class="text-center text-[10px] text-white/20 mt-5 lg:hidden">
                    v1.0 · &copy; {{ date('Y') }} {{ $brand['company_ar'] }}
                </p>
            </div>
        </main>
    </div>
</body>
</html>
