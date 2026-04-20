@extends('layouts.admin')

@section('title', $mode === 'create' ? 'إضافة مستخدم' : 'تعديل المستخدم')

@section('breadcrumbs')
    <a href="{{ route('admin.users.index') }}" class="bc-link">المستخدمون</a>
    <span class="bc-sep">‹</span>
    <span class="bc-current">{{ $mode === 'create' ? 'إضافة مستخدم جديد' : 'تعديل: ' . $user->full_name }}</span>
@endsection

@section('content')
    <form id="user-form" method="POST" class="max-w-3xl"
          action="{{ $mode === 'create' ? route('admin.users.store') : route('admin.users.update', $user) }}">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif

        <div class="card-glass rounded-2xl p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-5">البيانات الأساسية</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">الاسم الكامل <span class="text-rose-500">*</span></label>
                    <input name="full_name" value="{{ old('full_name', $user->full_name) }}"
                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="الاسم كاملاً" required>
                    <p data-error-for="full_name" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                    <input name="email" type="email" dir="ltr" value="{{ old('email', $user->email) }}"
                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="user@gedco.ps" required>
                    <p data-error-for="email" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">الهاتف</label>
                    <input name="phone" dir="ltr" value="{{ old('phone', $user->phone) }}"
                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="+970566700000">
                    <p data-error-for="phone" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">الحالة</label>
                    <label class="flex items-center gap-2.5 py-2.5">
                        <input type="checkbox" name="is_active" value="1"
                               @checked(old('is_active', $user->is_active ?? true))
                               class="w-4 h-4 rounded" style="accent-color: var(--accent-color)">
                        <span class="text-sm text-slate-700">الحساب نشط (يمكنه تسجيل الدخول)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card-glass rounded-2xl p-6 mb-5">
            <h3 class="font-semibold text-slate-900 mb-1">كلمة المرور</h3>
            <p class="text-xs text-slate-500 mb-5">
                @if ($mode === 'edit') اتركها فارغة للإبقاء على كلمة المرور الحالية. @else 8 أحرف على الأقل. @endif
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">كلمة المرور @if ($mode === 'create') <span class="text-rose-500">*</span>@endif</label>
                    <input name="password" type="password"
                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="8 أحرف على الأقل" {{ $mode === 'create' ? 'required' : '' }}>
                    <p data-error-for="password" class="hidden mt-1 text-xs text-rose-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">تأكيد كلمة المرور</label>
                    <input name="password_confirmation" type="password"
                           class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="أعد كتابتها">
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn-ghost flex-1 text-center px-4 py-3 rounded-xl font-medium">إلغاء</a>
            <button type="submit" id="submit-btn" class="btn-accent flex-1 py-3 px-4 rounded-xl text-sm flex items-center justify-center gap-2">
                <span id="btn-text">{{ $mode === 'create' ? 'إنشاء المستخدم' : 'حفظ التغييرات' }}</span>
                <span id="btn-spinner" class="hidden w-5 h-5 rounded-full spinner animate-spin"></span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $form = $('#user-form');
            const $btn = $('#submit-btn');
            const $btnText = $('#btn-text');
            const $btnSpinner = $('#btn-spinner');

            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
                $btn.prop('disabled', true);
                $btnText.text('جاري الحفظ...');
                $btnSpinner.removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(r => {
                    toastr.success(r.message);
                    setTimeout(() => window.location.href = r.redirect, 600);
                }).fail(xhr => {
                    $btn.prop('disabled', false);
                    $btnText.text('{{ $mode === "create" ? "إنشاء المستخدم" : "حفظ التغييرات" }}');
                    $btnSpinner.addClass('hidden');

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
