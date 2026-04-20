@extends('layouts.auth')

@section('title', 'كلمة مرور جديدة')
@section('heading', 'اختر كلمة مرور جديدة')
@section('subheading', 'تم التحقق من هويتك. الآن اختر كلمة مرور جديدة لحسابك')

@section('content')
    <form id="reset-form" method="POST" action="{{ route('password.sms.reset') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label class="block text-[12px] font-semibold text-white/60 mb-1.5">كلمة المرور الجديدة</label>
            <input name="password" type="password" required autofocus autocomplete="new-password"
                   class="input-glass w-full rounded-xl px-4 py-3.5 text-sm"
                   placeholder="٨ أحرف على الأقل">
            <p data-error-for="password" class="hidden mt-1.5 text-xs text-rose-400"></p>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-white/60 mb-1.5">تأكيد كلمة المرور</label>
            <input name="password_confirmation" type="password" required autocomplete="new-password"
                   class="input-glass w-full rounded-xl px-4 py-3.5 text-sm">
        </div>

        <button type="submit" id="submit-btn"
                class="btn-accent has-ripple w-full text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2 mt-4">
            <span id="btn-spinner" class="hidden w-[18px] h-[18px] rounded-full spinner animate-spin"></span>
            <span id="btn-text">تحديث كلمة المرور</span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#reset-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جارٍ التحديث...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    setTimeout(() => { window.location.href = r.redirect; }, 700);
                }).fail(xhr => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('تحديث كلمة المرور');
                    $('#btn-spinner').addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(f => {
                            $form.find(`[data-error-for="${f}"]`).text(xhr.responseJSON.errors[f][0]).removeClass('hidden');
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message ?? 'فشل التحديث');
                    }
                });
            });
        });
    </script>
@endsection
