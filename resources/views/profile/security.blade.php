@extends('profile._layout')

@section('title', 'الأمان')

@section('content')
    <div class="card p-6 mb-5">
        <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="flex-1">
                <h2 class="font-semibold text-slate-900">التحقق الثنائي (2FA) عبر SMS</h2>
                <p class="text-xs text-slate-500 mt-0.5">طبقة أمان إضافية — عند كل تسجيل دخول، يُرسَل رمز إلى هاتفك</p>
            </div>
            @if ($user->sms_2fa_enabled)
                <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> مُفعّل</span>
            @else
                <span class="badge badge-danger"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> غير مفعّل</span>
            @endif
        </div>

        @if ($user->sms_2fa_enabled)
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <div class="text-sm">
                        <p class="font-semibold text-emerald-900">الحماية الثنائية فعّالة</p>
                        <p class="text-emerald-700 text-xs mt-1">سيُطلَب منك إدخال رمز SMS عند كل تسجيل دخول.</p>
                        @if ($user->sms_2fa_enabled_at)
                            <p class="text-emerald-600 text-[11px] mt-1">مُفعّل منذ: {{ $user->sms_2fa_enabled_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <button type="button" id="disable-btn" class="btn-ghost text-rose-600 border-rose-200 hover:bg-rose-50 hover:border-rose-300 px-4 py-2.5 rounded-xl text-sm font-semibold">
                إيقاف الحماية الثنائية
            </button>
        @else
            @if (empty($user->phone))
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="text-sm">
                            <p class="font-semibold text-amber-900">أضف رقم هاتفك أولاً</p>
                            <p class="text-amber-700 text-xs mt-1">لا يمكن تفعيل التحقق الثنائي بدون رقم هاتف موثّق.</p>
                            <a href="{{ route('profile.edit') }}" class="inline-block mt-2 text-amber-800 hover:underline text-xs font-semibold">← أضف هاتفك الآن</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-slate-700 mb-1">سنرسل رمز تحقق إلى:</p>
                    <p class="text-base font-semibold text-slate-900 font-mono" dir="ltr">{{ $user->phone }}</p>
                </div>

                <div id="step-1">
                    <button type="button" id="enable-btn" class="btn-accent px-5 py-3 rounded-xl text-sm font-semibold">
                        تفعيل الحماية الثنائية
                    </button>
                </div>

                <div id="step-2" class="hidden space-y-3">
                    <p class="text-sm text-slate-600">أدخل الرمز المُرسَل إلى هاتفك:</p>
                    <input type="text" id="verify-code" inputmode="numeric" maxlength="6" dir="ltr"
                           class="input-clean text-center text-xl tracking-[.5em] font-bold" placeholder="••••••">
                    <p data-error-for="code" class="hidden text-xs text-rose-500"></p>
                    <div class="flex gap-2">
                        <button type="button" id="cancel-btn" class="btn-ghost px-4 py-2.5 rounded-xl text-sm">إلغاء</button>
                        <button type="button" id="verify-btn" class="btn-accent flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold">تحقّق وفعّل</button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#enable-btn').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true).text('جاري الإرسال...');

                $.post('{{ route("profile.2fa.enable.send") }}')
                    .done(r => {
                        toastr.success(r.message);
                        $('#step-1').addClass('hidden');
                        $('#step-2').removeClass('hidden');
                        $('#verify-code').focus();
                    })
                    .fail(xhr => { toastr.error(xhr.responseJSON?.message ?? 'فشل الإرسال'); $btn.prop('disabled', false).text('تفعيل الحماية الثنائية'); });
            });

            $('#cancel-btn').on('click', function () {
                $('#step-2').addClass('hidden');
                $('#step-1').removeClass('hidden');
                $('#enable-btn').prop('disabled', false).text('تفعيل الحماية الثنائية');
                $('#verify-code').val('');
            });

            $('#verify-code').on('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 6); });

            $('#verify-btn').on('click', function () {
                const code = $('#verify-code').val();
                if (code.length !== 6) return toastr.warning('أدخل 6 أرقام');

                const $btn = $(this);
                $btn.prop('disabled', true).text('جاري التحقق...');

                $.post('{{ route("profile.2fa.enable.verify") }}', { code })
                    .done(r => { toastr.success(r.message); setTimeout(() => location.reload(), 700); })
                    .fail(xhr => {
                        $btn.prop('disabled', false).text('تحقّق وفعّل');
                        if (xhr.responseJSON?.errors?.code) {
                            $('[data-error-for="code"]').text(xhr.responseJSON.errors.code[0]).removeClass('hidden');
                        } else {
                            toastr.error(xhr.responseJSON?.message ?? 'فشل التحقق');
                        }
                    });
            });

            $('#disable-btn').on('click', function () {
                Swal.fire({
                    title: 'إيقاف الحماية الثنائية؟',
                    html: 'هذا سيُضعف أمان حسابك. أدخل كلمة مرورك الحالية للتأكيد:',
                    input: 'password',
                    inputPlaceholder: 'كلمة المرور الحالية',
                    inputAttributes: { dir: 'ltr' },
                    showCancelButton: true,
                    confirmButtonText: 'نعم، أوقف',
                    cancelButtonText: 'إلغاء',
                    preConfirm: (password) => {
                        if (!password) { Swal.showValidationMessage('كلمة المرور مطلوبة'); return false; }
                        return $.post('{{ route("profile.2fa.disable") }}', { password })
                            .done(r => r)
                            .fail(xhr => { Swal.showValidationMessage(xhr.responseJSON?.errors?.password?.[0] ?? xhr.responseJSON?.message ?? 'فشل'); return false; });
                    },
                }).then(r => {
                    if (r.isConfirmed && r.value?.success) {
                        toastr.success(r.value.message);
                        setTimeout(() => location.reload(), 700);
                    }
                });
            });
        });
    </script>
@endsection
