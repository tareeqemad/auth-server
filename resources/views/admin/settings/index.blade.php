@extends('layouts.admin')

@section('title', 'الإعدادات العامة')

@section('breadcrumbs')
    <span class="bc-current">الإعدادات العامة</span>
@endsection

@section('content')
    @php
        $groupLabels = [
            'branding' => ['الهوية البصرية', 'معلومات الشركة والألوان والشعار'],
            'contact' => ['معلومات الاتصال', 'هاتف وبريد الدعم الفني'],
            'security' => ['الأمان', 'إعدادات الجلسات وكلمات المرور'],
            'sms' => ['رسائل SMS (Hotsms)', 'بيانات اعتماد مزوّد الرسائل النصية'],
            'general' => ['عامة', ''],
        ];
        $groupIcons = [
            'branding' => ['violet', 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            'contact' => ['blue', 'M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z'],
            'security' => ['emerald', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            'sms' => ['sky', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            'general' => ['slate', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        ];
    @endphp

    <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}" class="max-w-4xl space-y-5">
        @csrf

        @foreach ($groups as $groupName => $items)
            @php $info = $groupLabels[$groupName] ?? [$groupName, '']; @endphp
            @php $icon = $groupIcons[$groupName] ?? $groupIcons['general']; @endphp
            <div class="card-glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-{{ $icon[0] }}-50 text-{{ $icon[0] }}-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon[1] }}"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $info[0] }}</h3>
                        <p class="text-xs text-slate-500">{{ $info[1] }}</p>
                    </div>
                </div>

                @if ($groupName === 'sms')
                    <div class="mb-5 p-4 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-white border border-sky-200 text-sky-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">الرصيد الحالي</p>
                                <p class="text-xl font-bold text-slate-900" id="sms-balance">...</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="refresh-balance-btn" class="btn-ghost text-xs px-3 py-2 rounded-lg">
                                <svg class="w-3.5 h-3.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                تحديث
                            </button>
                            <button type="button" id="test-sms-btn" class="btn-accent text-xs px-3 py-2 rounded-lg">
                                اختبار إرسال SMS
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($items as $setting)
                        <div class="{{ $setting->type === 'text' ? 'md:col-span-2' : '' }}">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                {{ $setting->label ?? $setting->key }}
                                <code class="mr-1 text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">{{ $setting->key }}</code>
                            </label>

                            @if ($setting->key === 'primary_color' || $setting->key === 'accent_color')
                                <div class="flex gap-2 items-center">
                                    <input type="color" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                           class="w-12 h-10 rounded-lg cursor-pointer" style="border: 1.5px solid #e2e8f0;">
                                    <input type="text" readonly value="{{ $setting->value }}" dir="ltr"
                                           class="input-glass flex-1 px-3 py-2 rounded-lg text-xs font-mono">
                                </div>
                            @elseif ($setting->type === 'boolean')
                                <label class="flex items-center gap-2.5 py-2.5">
                                    <input type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                           @checked($setting->value)
                                           class="w-4 h-4 rounded" style="accent-color: var(--accent-color)">
                                    <span class="text-sm text-slate-700">مُفعّل</span>
                                </label>
                            @elseif ($setting->type === 'integer')
                                <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                       class="input-glass w-full px-3 py-2.5 rounded-lg text-sm">
                            @elseif ($setting->type === 'password')
                                <div class="relative">
                                    <input type="password" name="settings[{{ $setting->key }}]" value=""
                                           dir="ltr"
                                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm pl-10"
                                           placeholder="••••••••  (اتركه فارغاً للإبقاء)">
                                    <button type="button" onclick="const i=this.previousElementSibling; i.type=i.type==='password'?'text':'password';"
                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                       dir="{{ preg_match('/[A-Za-z]/', $setting->value ?? '') && ! preg_match('/[\x{0600}-\x{06FF}]/u', $setting->value ?? '') ? 'ltr' : 'auto' }}"
                                       class="input-glass w-full px-3 py-2.5 rounded-lg text-sm">
                            @endif

                            @if ($setting->description)
                                <p class="mt-1 text-[11px] text-slate-500">{{ $setting->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="sticky bottom-4 flex justify-end">
            <button type="submit" id="submit-btn" class="btn-accent py-3 px-8 rounded-xl text-sm flex items-center gap-2">
                <span id="btn-text">حفظ الإعدادات</span>
                <span id="btn-spinner" class="hidden w-5 h-5 rounded-full spinner animate-spin"></span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#settings-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جاري الحفظ...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    setTimeout(() => location.reload(), 700);
                }).fail(() => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('حفظ الإعدادات');
                    $('#btn-spinner').addClass('hidden');
                    toastr.error('فشل الحفظ');
                });
            });

            $('input[type="color"]').on('input', function () {
                $(this).next('input[type="text"]').val($(this).val());
            });

            function loadBalance() {
                $('#sms-balance').text('...');
                $.getJSON('{{ route("admin.settings.sms_balance") }}', r => {
                    if (r.ok) $('#sms-balance').text(r.balance.toLocaleString('ar-EG') + ' رسالة');
                    else $('#sms-balance').html('<span class="text-rose-600 text-sm">' + (r.error ?? 'فشل') + '</span>');
                }).fail(() => $('#sms-balance').html('<span class="text-rose-600 text-sm">فشل الاتصال</span>'));
            }

            if ($('#sms-balance').length) loadBalance();
            $('#refresh-balance-btn').on('click', loadBalance);

            $('#test-sms-btn').on('click', function () {
                Swal.fire({
                    title: 'اختبار إرسال SMS',
                    html: `<p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:.5rem">أدخل رقم هاتف لإرسال رسالة تجريبية</p>`,
                    input: 'tel',
                    inputPlaceholder: '+970566700000',
                    inputAttributes: { dir: 'ltr' },
                    showCancelButton: true,
                    confirmButtonText: 'إرسال',
                    cancelButtonText: 'إلغاء',
                    preConfirm: (phone) => {
                        if (! phone || phone.length < 6) {
                            Swal.showValidationMessage('أدخل رقماً صحيحاً');
                            return false;
                        }
                        return $.ajax({
                            url: '{{ route("admin.settings.test_sms") }}', type: 'POST',
                            data: { phone }, dataType: 'json',
                        }).done(r => r).fail(xhr => {
                            Swal.showValidationMessage(xhr.responseJSON?.message ?? 'فشل الإرسال');
                            return false;
                        });
                    },
                }).then(r => {
                    if (r.isConfirmed && r.value?.success) {
                        toastr.success(r.value.message);
                        loadBalance();
                    }
                });
            });
        });
    </script>
@endsection
