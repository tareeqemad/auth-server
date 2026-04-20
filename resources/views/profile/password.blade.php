@extends('profile._layout')

@section('title', 'تغيير كلمة المرور')

@section('content')
    <form id="password-form" method="POST" action="{{ route('profile.password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">تغيير كلمة المرور</h2>
                    <p class="text-xs text-slate-500">اختر كلمة مرور قوية تختلف عن الحالية</p>
                </div>
            </div>

            <div class="space-y-4 max-w-lg">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">كلمة المرور الحالية <span class="text-rose-500">*</span></label>
                    <input name="current_password" type="password" class="input-clean" autocomplete="current-password" required>
                    <p data-error-for="current_password" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">كلمة المرور الجديدة <span class="text-rose-500">*</span></label>
                    <input name="password" type="password" class="input-clean" autocomplete="new-password" placeholder="٨ أحرف على الأقل" required>
                    <p data-error-for="password" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">تأكيد كلمة المرور <span class="text-rose-500">*</span></label>
                    <input name="password_confirmation" type="password" class="input-clean" autocomplete="new-password" required>
                </div>
            </div>
        </div>

        <div class="card p-5 bg-amber-50 border-amber-200">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-sm text-amber-800">
                    <p class="font-semibold mb-1">نصائح لكلمة مرور قوية:</p>
                    <ul class="text-xs space-y-0.5 text-amber-700 leading-relaxed">
                        <li>• ٨ أحرف على الأقل</li>
                        <li>• امزج بين الحروف الكبيرة والصغيرة والأرقام</li>
                        <li>• تجنّب المعلومات الشخصية (الاسم، تاريخ الميلاد)</li>
                        <li>• استخدم كلمة مرور فريدة لا تستخدمها في مواقع أخرى</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('profile.edit') }}" class="btn-ghost px-5 py-3 rounded-xl font-medium">إلغاء</a>
            <button type="submit" id="submit-btn" class="btn-accent px-6 py-3 rounded-xl text-sm flex items-center gap-2">
                <span id="btn-text">تحديث كلمة المرور</span>
                <span id="btn-spinner" class="hidden w-5 h-5 rounded-full spinner animate-spin"></span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#password-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جاري التحديث...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    $form[0].reset();
                    $('#btn-text').text('تحديث كلمة المرور');
                    $('#btn-spinner').addClass('hidden');
                    $('#submit-btn').prop('disabled', false);
                }).fail(xhr => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('تحديث كلمة المرور');
                    $('#btn-spinner').addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(f => {
                            $form.find(`[data-error-for="${f}"]`).text(xhr.responseJSON.errors[f][0]).removeClass('hidden');
                            $form.find(`[name="${f}"]`).addClass('error');
                        });
                        toastr.error('تحقق من الحقول');
                    } else {
                        toastr.error('فشل التحديث');
                    }
                });
            });
        });
    </script>
@endsection
