@extends('layouts.auth')

@section('title', 'استعادة عبر SMS')
@section('heading', 'استعادة كلمة المرور')
@section('subheading', 'أدخل رقم هاتفك وسنرسل لك رمز التحقق عبر SMS')

@section('content')
    <form id="sms-form" method="POST" action="{{ route('password.sms.send') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label class="block text-[12px] font-semibold text-white/60 mb-1.5">رقم الهاتف</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white/30">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/>
                    </svg>
                </span>
                <input name="phone" type="tel" dir="ltr" required autofocus
                       class="input-glass w-full rounded-xl pr-11 pl-4 py-3.5 text-sm"
                       placeholder="+970 5XX XXX XXX">
            </div>
            <p data-error-for="phone" class="hidden mt-1.5 text-xs text-rose-400"></p>
            <p class="mt-2 text-[11px] text-white/40">سنرسل رمز تحقق مكوّن من 6 أرقام</p>
        </div>

        <button type="submit" id="submit-btn"
                class="btn-accent has-ripple w-full text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2 mt-4">
            <svg id="btn-icon" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span id="btn-spinner" class="hidden w-[18px] h-[18px] rounded-full spinner animate-spin"></span>
            <span id="btn-text">إرسال الرمز</span>
        </button>
    </form>

    <div class="mt-5 text-center">
        <a href="{{ route('password.request') }}" class="text-xs font-medium text-white/40 hover:text-white/70 inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            استخدم البريد الإلكتروني بدلاً من ذلك
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#sms-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جارٍ الإرسال...');
                $('#btn-icon').addClass('hidden');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    setTimeout(() => { window.location.href = r.redirect; }, 800);
                }).fail(xhr => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('إرسال الرمز');
                    $('#btn-icon').removeClass('hidden');
                    $('#btn-spinner').addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(f => {
                            $form.find(`[data-error-for="${f}"]`).text(xhr.responseJSON.errors[f][0]).removeClass('hidden');
                            $form.find(`[name="${f}"]`).addClass('error');
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message ?? 'فشل الإرسال. حاول مرة أخرى.');
                    }
                });
            });
        });
    </script>
@endsection
