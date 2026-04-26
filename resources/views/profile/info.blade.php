@extends('profile._layout')

@section('title', 'بياناتي الشخصية')

@section('content')
    <form id="profile-form" method="POST" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">البيانات الأساسية</h2>
                    <p class="text-xs text-slate-500">عدّل معلوماتك الشخصية</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">الاسم الكامل <span class="text-rose-500">*</span></label>
                    <input name="full_name" value="{{ old('full_name', $user->full_name) }}" class="input-clean" required>
                    <p data-error-for="full_name" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                    <input name="email" type="email" dir="ltr" value="{{ old('email', $user->email) }}" class="input-clean" required>
                    <p class="mt-1 text-[11px] text-slate-500">تغيير البريد يتطلّب إعادة التأكيد</p>
                    <p data-error-for="email" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">رقم الهاتف</label>
                    <input name="phone" dir="ltr" value="{{ old('phone', $user->phone) }}" class="input-clean" placeholder="+970566700000">
                    <p data-error-for="phone" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        رقم الهوية
                        @if (! $user->national_id)
                            <span class="text-rose-500">*</span>
                        @endif
                    </label>
                    @if ($user->national_id)
                        <input dir="ltr" value="{{ $user->national_id }}" class="input-clean font-mono" readonly>
                        <p class="mt-1 text-[11px] text-slate-500">للتعديل، تواصل مع الإدارة.</p>
                    @else
                        <input name="national_id" dir="ltr" inputmode="numeric" maxlength="9" pattern="\d{9}"
                               value="{{ old('national_id') }}" class="input-clean font-mono" placeholder="9 أرقام">
                        <p class="mt-1 text-[11px] text-amber-600">⚠ يجب إضافة رقم الهوية لربط حسابك بالأنظمة.</p>
                        <p data-error-for="national_id" class="hidden mt-1 text-xs text-rose-500"></p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">معلومات للقراءة فقط</h2>
                    <p class="text-xs text-slate-500">لا يمكن تعديل هذه المعلومات</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <p class="text-[11px] text-slate-500 mb-1">آخر تسجيل دخول</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <p class="text-[11px] text-slate-500 mb-1">آخر IP</p>
                    <p class="text-sm font-medium text-slate-900 font-mono" dir="ltr">{{ $user->last_login_ip ?? '—' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <p class="text-[11px] text-slate-500 mb-1">تاريخ الإنشاء</p>
                    <p class="text-sm font-medium text-slate-900">{{ $user->created_at->format('Y-m-d') }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <p class="text-[11px] text-slate-500 mb-1">حالة الحساب</p>
                    <p class="text-sm font-medium">
                        @if ($user->is_active)
                            <span class="text-emerald-600 inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                نشط ومؤكّد
                            </span>
                        @else
                            <span class="text-rose-600">معطّل</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('dashboard') }}" class="btn-ghost px-5 py-3 rounded-xl font-medium">إلغاء</a>
            <button type="submit" id="submit-btn" class="btn-accent px-6 py-3 rounded-xl text-sm flex items-center gap-2">
                <span id="btn-text">حفظ التعديلات</span>
                <span id="btn-spinner" class="hidden w-5 h-5 rounded-full spinner animate-spin"></span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#profile-form');
            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
                $('#submit-btn').prop('disabled', true);
                $('#btn-text').text('جاري الحفظ...');
                $('#btn-spinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    $('#btn-text').text('حفظ التعديلات');
                    $('#btn-spinner').addClass('hidden');
                    $('#submit-btn').prop('disabled', false);
                }).fail(xhr => {
                    $('#submit-btn').prop('disabled', false);
                    $('#btn-text').text('حفظ التعديلات');
                    $('#btn-spinner').addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(f => {
                            $form.find(`[data-error-for="${f}"]`).text(xhr.responseJSON.errors[f][0]).removeClass('hidden');
                            $form.find(`[name="${f}"]`).addClass('error');
                        });
                        toastr.error('تحقق من الحقول');
                    } else {
                        toastr.error('فشل الحفظ');
                    }
                });
            });
        });
    </script>
@endsection
