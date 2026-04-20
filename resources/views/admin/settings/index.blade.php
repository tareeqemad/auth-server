@extends('layouts.admin')

@section('title', 'الإعدادات العامة')

@section('breadcrumbs')
    <span class="bc-current">الإعدادات العامة</span>
@endsection

@section('content')
    @php
        $tabs = [
            'branding' => ['title' => 'الهوية البصرية', 'desc' => 'اسم الشركة والألوان والشعار', 'color' => '#8b5cf6',
                'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            'contact' => ['title' => 'معلومات الاتصال', 'desc' => 'هاتف وبريد الدعم الفني', 'color' => '#3b82f6',
                'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z'],
            'security' => ['title' => 'الأمان', 'desc' => 'الجلسات وكلمات المرور', 'color' => '#10b981',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            'sms' => ['title' => 'رسائل SMS', 'desc' => 'Hotsms — رصيد واختبار', 'color' => '#0ea5e9',
                'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            'general' => ['title' => 'عامة', 'desc' => 'إعدادات متنوّعة', 'color' => '#64748b',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        ];
        $firstTab = collect($groups)->keys()->first();
    @endphp

    <style>
        .settings-shell {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 900px) { .settings-shell { grid-template-columns: 1fr; } }

        .settings-nav {
            position: sticky; top: 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: .75rem;
        }
        .settings-nav-item {
            display: flex; align-items: center; gap: .75rem;
            padding: .75rem .85rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all .15s;
            color: #475569;
            user-select: none;
            margin-bottom: .25rem;
        }
        .settings-nav-item:hover { background: #f8fafc; color: #0f172a; }
        .settings-nav-item.active {
            background: linear-gradient(135deg, rgba(249,115,22,.08), rgba(251,191,36,.08));
            color: #9a3412;
            font-weight: 600;
            box-shadow: inset 3px 0 0 #F97316;
        }
        .settings-nav-item .ico {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .settings-nav-item .lbl { font-size: 13px; line-height: 1.2; }
        .settings-nav-item .desc { font-size: 10px; color: #94a3b8; margin-top: 1px; }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .section-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
        }
        .section-head {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 1rem;
        }
        .section-head .big-ico {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .section-body { padding: 1.5rem; }

        .field-group { margin-bottom: 1.5rem; }
        .field-group:last-child { margin-bottom: 0; }
        .field-label {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 12px; font-weight: 600; color: #334155;
            margin-bottom: .4rem;
        }
        .field-label .key-code {
            font-family: monospace; font-size: 10px;
            color: #64748b;
            background: #f1f5f9;
            padding: .1rem .5rem;
            border-radius: 4px;
        }
        .field-hint { font-size: 11px; color: #64748b; margin-top: .35rem; }

        .input-modern {
            width: 100%;
            padding: .65rem .85rem;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            color: #0f172a;
            transition: all .15s;
        }
        .input-modern:focus {
            outline: none;
            border-color: #F97316;
            box-shadow: 0 0 0 3px rgba(249,115,22,.1);
        }

        .toggle-switch {
            position: relative;
            display: inline-flex; align-items: center;
            width: 44px; height: 24px;
            background: #cbd5e1;
            border-radius: 999px;
            cursor: pointer;
            transition: background .2s;
            flex-shrink: 0;
        }
        .toggle-switch.on { background: #10b981; }
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px; right: 2px;
            width: 20px; height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.15);
        }
        .toggle-switch.on::after { transform: translateX(-20px); }
        .toggle-switch input { display: none; }
        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: .85rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .color-picker-wrap {
            display: flex; gap: .5rem; align-items: center;
            padding: .35rem;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
        }
        .color-picker-wrap:focus-within { border-color: #F97316; }
        .color-swatch {
            width: 40px; height: 40px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            padding: 0;
            overflow: hidden;
        }

        .password-wrap { position: relative; }
        .password-toggle {
            position: absolute;
            left: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: .3rem;
        }
        .password-toggle:hover { color: #0f172a; }

        /* Preview palette */
        .preview-palette {
            display: flex; gap: .75rem; align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
            margin-top: .75rem;
        }
        .preview-btn {
            padding: .6rem 1.2rem;
            border-radius: 10px;
            color: white; font-size: 13px; font-weight: 600;
            border: none;
        }

        /* SMS balance hero */
        .sms-hero {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .sms-hero::before {
            content: '';
            position: absolute;
            top: -20%; right: -5%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,.18), transparent 60%);
        }
        .sms-hero .balance-num {
            font-size: 2.5rem; font-weight: 800;
            line-height: 1;
            letter-spacing: -.02em;
        }
        .sms-hero .balance-lbl {
            font-size: 11px; opacity: .8;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: .35rem;
        }
        .sms-btn {
            padding: .55rem 1rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 8px;
            color: white; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all .15s;
        }
        .sms-btn:hover { background: rgba(255,255,255,.25); }
        .sms-btn.solid {
            background: white; color: #0369a1;
        }

        /* Sticky save bar */
        .save-bar {
            position: sticky;
            bottom: 1rem;
            margin-top: 1.5rem;
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(10px);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: .85rem 1.25rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            z-index: 10;
        }
        .save-bar .hint { font-size: 12px; color: #64748b; }
        .save-bar button {
            padding: .7rem 2rem;
            border-radius: 10px;
            font-weight: 700; font-size: 14px;
            color: white;
            background: linear-gradient(135deg,#F97316,#FBBF24);
            border: none;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: .5rem;
            box-shadow: 0 6px 16px rgba(249,115,22,.35);
        }
        .save-bar button:disabled { opacity: .7; cursor: wait; }
    </style>

    <div class="settings-shell">
        {{-- ═════════════════ SIDE NAV ═════════════════ --}}
        <nav class="settings-nav" id="settings-tabs">
            @foreach ($groups as $groupName => $items)
                @php $t = $tabs[$groupName] ?? ['title' => $groupName, 'desc' => '', 'color' => '#64748b', 'icon' => $tabs['general']['icon']]; @endphp
                <a href="#tab-{{ $groupName }}" class="settings-nav-item {{ $groupName === $firstTab ? 'active' : '' }}" data-tab="{{ $groupName }}">
                    <div class="ico" style="background: {{ $t['color'] }}1a; color: {{ $t['color'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                    </div>
                    <div>
                        <p class="lbl">{{ $t['title'] }}</p>
                        <p class="desc">{{ $t['desc'] }}</p>
                    </div>
                </a>
            @endforeach
        </nav>

        {{-- ═════════════════ FORM ═════════════════ --}}
        <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            @foreach ($groups as $groupName => $items)
                @php $t = $tabs[$groupName] ?? ['title' => $groupName, 'desc' => '', 'color' => '#64748b', 'icon' => $tabs['general']['icon']]; @endphp
                <div class="tab-panel {{ $groupName === $firstTab ? 'active' : '' }}" id="tab-{{ $groupName }}" data-panel="{{ $groupName }}">

                    @if ($groupName === 'sms')
                        {{-- SMS balance hero --}}
                        <div class="sms-hero">
                            <div class="relative z-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                                <div>
                                    <p class="balance-lbl">💬 الرصيد الحالي — Hotsms</p>
                                    <p class="balance-num" id="sms-balance">…</p>
                                    <p class="text-xs opacity-70 mt-1">كل رسالة ≈ 160 حرف إنجليزي / 70 عربي</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" id="refresh-balance-btn" class="sms-btn">
                                        <svg class="w-3.5 h-3.5 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        تحديث
                                    </button>
                                    <button type="button" id="test-sms-btn" class="sms-btn solid">
                                        ✉️ إرسال رسالة اختبار
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="section-card">
                        <div class="section-head">
                            <div class="big-ico" style="background: {{ $t['color'] }}1a; color: {{ $t['color'] }}">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate-900">{{ $t['title'] }}</h2>
                                <p class="text-xs text-slate-500">{{ $t['desc'] }}</p>
                            </div>
                        </div>

                        <div class="section-body">
                            <div class="grid grid-cols-1 {{ $groupName === 'branding' ? 'md:grid-cols-1' : 'md:grid-cols-2' }} gap-5">
                                @foreach ($items as $setting)
                                    @php
                                        $isFull = in_array($setting->type, ['text','textarea']) || in_array($setting->key, ['logo_url','system_tagline']);
                                        $isColor = in_array($setting->key, ['primary_color','accent_color']);
                                    @endphp

                                    <div class="field-group {{ $isFull ? 'md:col-span-2' : '' }}">
                                        <label class="field-label">
                                            <span>{{ $setting->label ?? $setting->key }}</span>
                                            <code class="key-code" dir="ltr">{{ $setting->key }}</code>
                                        </label>

                                        @if ($isColor)
                                            <div class="color-picker-wrap">
                                                <input type="color" name="settings[{{ $setting->key }}]"
                                                       value="{{ $setting->value }}"
                                                       class="color-swatch"
                                                       data-sync="#color-{{ $setting->key }}">
                                                <input id="color-{{ $setting->key }}" type="text" readonly
                                                       value="{{ $setting->value }}" dir="ltr"
                                                       style="flex:1;padding:.5rem;border:0;background:transparent;font-family:monospace;font-size:13px;color:#334155;outline:none">
                                            </div>
                                        @elseif ($setting->type === 'boolean')
                                            <label class="toggle-row">
                                                <span class="text-sm text-slate-700">{{ $setting->description ?: 'مُفعّل' }}</span>
                                                <span class="toggle-switch {{ $setting->value ? 'on' : '' }}" data-toggle>
                                                    <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" @checked($setting->value)>
                                                </span>
                                            </label>
                                        @elseif ($setting->type === 'integer')
                                            <input type="number" name="settings[{{ $setting->key }}]"
                                                   value="{{ $setting->value }}"
                                                   class="input-modern" dir="ltr">
                                        @elseif ($setting->type === 'password')
                                            <div class="password-wrap">
                                                <input type="password" name="settings[{{ $setting->key }}]" value="" dir="ltr"
                                                       class="input-modern" style="padding-left: 2.5rem"
                                                       placeholder="●●●●●●●●  (اتركه فارغاً للإبقاء)">
                                                <button type="button" class="password-toggle" data-toggle-pw>
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                            </div>
                                        @else
                                            <input type="text" name="settings[{{ $setting->key }}]"
                                                   value="{{ $setting->value }}"
                                                   dir="{{ preg_match('/[A-Za-z]/', $setting->value ?? '') && ! preg_match('/[\x{0600}-\x{06FF}]/u', $setting->value ?? '') ? 'ltr' : 'auto' }}"
                                                   class="input-modern">
                                        @endif

                                        @if ($setting->description && $setting->type !== 'boolean')
                                            <p class="field-hint">{{ $setting->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($groupName === 'branding' && collect($items)->contains(fn ($s) => in_array($s->key, ['primary_color','accent_color'])))
                                @php
                                    $primary = collect($items)->firstWhere('key', 'primary_color')?->value ?? '#0F2440';
                                    $accent = collect($items)->firstWhere('key', 'accent_color')?->value ?? '#F97316';
                                @endphp
                                <div class="preview-palette">
                                    <span class="text-xs text-slate-500 shrink-0">معاينة:</span>
                                    <button type="button" class="preview-btn" style="background: {{ $primary }}">الأساسي</button>
                                    <button type="button" class="preview-btn" style="background: linear-gradient(135deg, {{ $accent }}, #FBBF24)">التمييزي</button>
                                    <div class="flex-1"></div>
                                    <code class="text-[11px] text-slate-400" dir="ltr">{{ $primary }} / {{ $accent }}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="save-bar">
                <span class="hint">✨ سيتم تطبيق التغييرات فور الحفظ.</span>
                <button type="submit" id="submit-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span id="btn-text">حفظ الإعدادات</span>
                    <span id="btn-spinner" class="hidden w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ═════ Tab switching ═════
            $('#settings-tabs .settings-nav-item').on('click', function (e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                $('#settings-tabs .settings-nav-item').removeClass('active');
                $(this).addClass('active');
                $('.tab-panel').removeClass('active');
                $('[data-panel="' + tab + '"]').addClass('active');
                history.replaceState(null, '', '#tab-' + tab);
            });

            // Activate tab from URL hash if present
            var hash = (window.location.hash || '').replace('#tab-', '');
            if (hash) $('#settings-tabs .settings-nav-item[data-tab="' + hash + '"]').trigger('click');

            // ═════ Color picker sync ═════
            $('input[type="color"][data-sync]').on('input', function () {
                $($(this).data('sync')).val($(this).val());

                // Update live preview
                var key = $(this).attr('name').replace('settings[', '').replace(']', '');
                if (key === 'primary_color') $('.preview-btn').eq(0).css('background', $(this).val());
                if (key === 'accent_color') $('.preview-btn').eq(1).css('background', 'linear-gradient(135deg, ' + $(this).val() + ', #FBBF24)');
            });

            // ═════ Toggle switches ═════
            $(document).on('click', '[data-toggle]', function (e) {
                e.preventDefault();
                var $sw = $(this);
                var $input = $sw.find('input[type="checkbox"]');
                $sw.toggleClass('on');
                $input.prop('checked', $sw.hasClass('on'));
            });

            // ═════ Password show/hide ═════
            $(document).on('click', '[data-toggle-pw]', function () {
                var $inp = $(this).siblings('input');
                $inp.attr('type', $inp.attr('type') === 'password' ? 'text' : 'password');
            });

            // ═════ Form submit ═════
            var $form = $('#settings-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جاري الحفظ...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' }
                })
                    .done(function (r) {
                        toastr.success(r.message || 'تم الحفظ');
                        setTimeout(function () { location.reload(); }, 700);
                    })
                    .fail(function () {
                        $('#submit-btn').prop('disabled', false);
                        $('#btn-text').text('حفظ الإعدادات');
                        $('#btn-spinner').addClass('hidden');
                        toastr.error('فشل الحفظ');
                    });
            });

            // ═════ SMS balance ═════
            function loadBalance() {
                var $el = $('#sms-balance');
                if (! $el.length) return;
                $el.text('…');
                $.getJSON('{{ route("admin.settings.sms_balance") }}', function (r) {
                    if (r.ok) {
                        $el.text(Number(r.balance).toLocaleString('en-US'));
                    } else {
                        $el.html('<span style="font-size:1rem">' + (r.error || 'فشل') + '</span>');
                    }
                }).fail(function () {
                    $el.html('<span style="font-size:1rem;opacity:.7">فشل الاتصال</span>');
                });
            }
            if ($('#sms-balance').length) loadBalance();
            $('#refresh-balance-btn').on('click', loadBalance);

            // ═════ Test SMS ═════
            $('#test-sms-btn').on('click', function () {
                Swal.fire({
                    title: 'اختبار إرسال SMS',
                    html: '<p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:.5rem">أدخل رقم هاتف لاستلام رسالة تجريبية</p>',
                    input: 'tel',
                    inputPlaceholder: '+970566700000',
                    inputAttributes: { dir: 'ltr' },
                    showCancelButton: true,
                    confirmButtonText: 'إرسال',
                    cancelButtonText: 'إلغاء',
                    preConfirm: function (phone) {
                        if (!phone || phone.length < 6) {
                            Swal.showValidationMessage('أدخل رقماً صحيحاً');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.settings.test_sms") }}', type: 'POST',
                            data: { phone: phone }, dataType: 'json'
                        }).done(function (r) { return r; })
                          .fail(function (xhr) {
                              Swal.showValidationMessage((xhr.responseJSON && xhr.responseJSON.message) || 'فشل الإرسال');
                              return false;
                          });
                    }
                }).then(function (r) {
                    if (r.isConfirmed && r.value && r.value.success) {
                        toastr.success(r.value.message);
                        loadBalance();
                    }
                });
            });
        });
    </script>
@endsection
