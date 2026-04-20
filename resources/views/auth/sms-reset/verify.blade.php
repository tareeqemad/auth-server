@extends('layouts.auth')

@section('title', 'إدخال رمز التحقق')
@section('heading', 'أدخل رمز التحقق')
@section('subheading', 'أرسلنا لك رمزاً من 6 أرقام عبر SMS')

@section('content')
    <form id="verify-form" method="POST" action="{{ route('password.sms.verify') }}" class="space-y-4" novalidate>
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">

        <div class="bg-white/[0.04] border border-white/[0.08] rounded-xl p-3.5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="min-w-0 text-sm">
                <p class="text-white/90">تم إرسال الرمز إلى</p>
                <p class="text-white/50 text-xs font-mono" dir="ltr">+{{ $phone }}</p>
            </div>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-white/60 mb-2">رمز التحقق</label>
            <input name="code" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus
                   dir="ltr"
                   class="input-glass w-full rounded-xl px-4 py-4 text-center text-2xl tracking-[.5em] font-bold"
                   placeholder="••••••">
            <p data-error-for="code" class="hidden mt-2 text-xs text-rose-400"></p>
            <p class="mt-3 text-[11px] text-white/40 text-center">
                لم يصلك الرمز؟
                <a href="{{ route('password.sms.phone') }}" class="text-amber-400 hover:underline font-semibold">أعد المحاولة</a>
            </p>
        </div>

        <button type="submit" id="submit-btn"
                class="btn-accent has-ripple w-full text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2 mt-4">
            <span id="btn-spinner" class="hidden w-[18px] h-[18px] rounded-full spinner animate-spin"></span>
            <span id="btn-text">تحقّق</span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#verify-form');

            $('input[name="code"]').on('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
                if (this.value.length === 6) $form.submit();
            });

            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جارٍ التحقق...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    setTimeout(() => { window.location.href = r.redirect; }, 400);
                }).fail(xhr => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('تحقّق');
                    $('#btn-spinner').addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(f => {
                            $form.find(`[data-error-for="${f}"]`).text(xhr.responseJSON.errors[f][0]).removeClass('hidden');
                            $form.find(`[name="${f}"]`).addClass('error');
                        });
                        $form.addClass('animate-shake');
                        setTimeout(() => $form.removeClass('animate-shake'), 400);
                    } else {
                        toastr.error('فشل التحقق.');
                    }
                });
            });
        });
    </script>

    <style>
        @keyframes shake { 0%,100% { transform: translateX(0); } 10%,30%,50%,70%,90% { transform: translateX(-4px); } 20%,40%,60%,80% { transform: translateX(4px); } }
        .animate-shake { animation: shake .4s cubic-bezier(.36,.07,.19,.97); }
    </style>
@endsection
