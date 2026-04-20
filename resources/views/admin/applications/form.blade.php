@extends('layouts.admin')

@section('title', $mode === 'create' ? 'إضافة نظام جديد' : 'تعديل النظام')

@section('breadcrumbs')
    <a href="{{ route('admin.applications.index') }}" class="bc-link">الأنظمة</a>
    <span class="bc-sep">‹</span>
    <span class="bc-current">{{ $mode === 'create' ? 'إضافة نظام جديد' : 'تعديل: ' . $application->displayName() }}</span>
@endsection

@section('content')
    <form id="app-form" method="POST"
          action="{{ $mode === 'create' ? route('admin.applications.store') : route('admin.applications.update', $application) }}"
          class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif

        <div class="lg:col-span-2 space-y-5">

            <div class="card-glass rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background: rgba(249,115,22,.12); color: #fdba74;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-semibold text-slate-900">البيانات الأساسية</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">المعرّف (slug) <span class="text-rose-500">*</span></label>
                        <input name="slug" value="{{ old('slug', $application->slug) }}" dir="ltr"
                               class="input-glass w-full px-3 py-2.5 rounded-lg font-mono text-sm"
                               placeholder="system_x" required pattern="[a-z0-9_]+">
                        <p class="mt-1 text-[11px] text-slate-500">حروف صغيرة + أرقام + شرطة سفلية فقط (مثل: <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#0f172a">system_a</code>)</p>
                        <p data-error-for="slug" class="hidden mt-1 text-xs text-rose-500"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">الاسم بالعربي <span class="text-rose-500">*</span></label>
                        <input name="display_name_ar" value="{{ old('display_name_ar', $application->display_name_ar) }}"
                               class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="النظام الأول" required>
                        <p data-error-for="display_name_ar" class="hidden mt-1 text-xs text-rose-500"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">الاسم بالإنجليزي</label>
                        <input name="display_name_en" value="{{ old('display_name_en', $application->display_name_en) }}" dir="ltr"
                               class="input-glass w-full px-3 py-2.5 rounded-lg text-sm" placeholder="System One">
                        <p data-error-for="display_name_en" class="hidden mt-1 text-xs text-rose-500"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">الوصف</label>
                        <textarea name="description" rows="2"
                                  class="input-glass w-full px-3 py-2.5 rounded-lg text-sm resize-none"
                                  placeholder="وصف قصير للنظام">{{ old('description', $application->description) }}</textarea>
                        <p data-error-for="description" class="hidden mt-1 text-xs text-rose-500"></p>
                    </div>
                </div>
            </div>

            <div class="card-glass rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             style="background: rgba(59,130,246,.12); color: #93c5fd;">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-900">روابط التحويل (Redirect URIs)</h3>
                    </div>
                    <button type="button" id="add-redirect"
                            class="btn-ghost text-xs font-medium px-3 py-1.5 rounded-lg flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        إضافة
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4">روابط OAuth callback في النظام العميل</p>

                <div id="redirect-uris-list" class="space-y-2">
                    @php
                        $uris = old('redirect_uris', $application->redirect_uris ?? []);
                        if (empty($uris)) $uris = [''];
                    @endphp
                    @foreach ($uris as $uri)
                        <div class="redirect-row flex gap-2">
                            <input name="redirect_uris[]" value="{{ $uri }}" type="url" dir="ltr"
                                   class="input-glass flex-1 px-3 py-2 rounded-lg font-mono text-xs"
                                   placeholder="https://app.example.com/auth/callback" required>
                            <button type="button" class="remove-redirect icon-btn danger">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <p data-error-for="redirect_uris" class="hidden mt-2 text-xs text-rose-500"></p>
            </div>

            <div class="card-glass rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background: rgba(16,185,129,.12); color: #6ee7b7;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">أنواع الدخول (Grant Types)</h3>
                        <p class="text-xs text-slate-500">الافتراضي مناسب لمعظم الأنظمة</p>
                    </div>
                </div>

                @php $grants = old('grant_types', $application->grant_types ?? ['authorization_code', 'refresh_token']); @endphp

                @foreach ([
                    'authorization_code' => ['دخول المستخدم عبر المتصفح (الافتراضي والموصى به)', true],
                    'refresh_token' => ['تجديد جلسة المستخدم تلقائياً دون إعادة تسجيل الدخول', true],
                    'client_credentials' => ['اتصال خادم ← خادم بدون مستخدم (APIs فقط)', false],
                ] as $key => [$desc, $recommended])
                    <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer transition hover:bg-slate-50"
                           style="border: 1px solid #e2e8f0; margin-bottom: .5rem;">
                        <input type="checkbox" name="grant_types[]" value="{{ $key }}"
                               @checked(in_array($key, $grants))
                               class="mt-1 w-4 h-4 rounded" style="accent-color: var(--accent-color)">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <code class="font-mono text-sm font-semibold text-slate-900">{{ $key }}</code>
                                @if ($recommended)
                                    <span class="badge badge-success !text-[10px] !py-0.5">موصى به</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $desc }}</p>
                        </div>
                    </label>
                @endforeach
                <p data-error-for="grant_types" class="hidden mt-2 text-xs text-rose-500"></p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card-glass rounded-2xl p-6">
                <h3 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background: rgba(139,92,246,.12); color: #c4b5fd;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    المظهر
                </h3>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">اللون المميّز</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" name="color" value="{{ old('color', $application->color ?? '#F97316') }}"
                               id="color-input"
                               class="w-12 h-10 rounded-lg cursor-pointer"
                               style="background: transparent; border: 1.5px solid #e2e8f0;">
                        <input type="text" id="color-text" value="{{ old('color', $application->color ?? '#F97316') }}"
                               dir="ltr" readonly
                               class="input-glass flex-1 px-3 py-2 rounded-lg font-mono text-xs cursor-default">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">رابط الشعار (اختياري)</label>
                    <input name="logo_url" value="{{ old('logo_url', $application->logo_url) }}" type="url" dir="ltr"
                           class="input-glass w-full px-3 py-2 rounded-lg text-xs" placeholder="https://...">
                </div>

                <p class="text-[11px] text-slate-500 mb-2">معاينة مباشرة:</p>
                <div class="flex items-center gap-3 p-3 rounded-xl"
                     style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div id="preview-badge" class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg"
                         style="background: linear-gradient(135deg, {{ old('color', $application->color ?? '#F97316') }}, color-mix(in srgb, {{ old('color', $application->color ?? '#F97316') }} 70%, black))">
                        <span id="preview-initial">{{ $application->initial() ?: 'N' }}</span>
                    </div>
                    <div class="min-w-0">
                        <p id="preview-name" class="font-semibold text-slate-900 truncate">{{ $application->display_name_ar ?: 'اسم النظام' }}</p>
                        <p id="preview-desc" class="text-xs text-slate-500 truncate">{{ $application->description ?: 'الوصف' }}</p>
                    </div>
                </div>
            </div>

            <div class="card-glass rounded-2xl p-6">
                <h3 class="font-semibold text-slate-900 mb-4">إعدادات متقدّمة</h3>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">رابط فتح النظام</label>
                    <input name="launch_url" value="{{ old('launch_url', $application->launch_url) }}" type="url" dir="ltr"
                           class="input-glass w-full px-3 py-2 rounded-lg text-xs" placeholder="https://app.example.com">
                    <p class="mt-1 text-[11px] text-slate-500">الرابط الذي يفتحه المستخدم من لوحته</p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        رابط Back-channel Logout
                        <span class="text-slate-400 font-normal">(اختياري — لتسجيل الخروج الموحّد)</span>
                    </label>
                    <input name="back_channel_logout_uri" value="{{ old('back_channel_logout_uri', $application->back_channel_logout_uri) }}"
                           type="url" dir="ltr"
                           class="input-glass w-full px-3 py-2 rounded-lg text-xs"
                           placeholder="https://app.example.com/sso/back-channel-logout">
                    <p class="mt-1 text-[11px] text-slate-500">
                        عند logout من IdP سيتم إرسال <code>logout_token</code> (JWT موقّع RS256) POST على هذا الرابط.
                        اتركه فارغاً للتعطيل.
                    </p>
                </div>

                <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer hover:bg-slate-50 transition"
                       style="border: 1px solid #e2e8f0;">
                    <input type="checkbox" name="is_first_party" value="1"
                           @checked(old('is_first_party', $application->is_first_party ?? true))
                           class="mt-0.5 w-4 h-4 rounded" style="accent-color: var(--accent-color)">
                    <div>
                        <p class="text-sm font-medium text-slate-900">نظام داخلي موثوق</p>
                        <p class="text-xs text-slate-500 mt-0.5">يتجاوز صفحة موافقة المستخدم (Consent)</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-2 sticky bottom-4">
                <a href="{{ route('admin.applications.index') }}" class="btn-ghost flex-1 text-center px-4 py-3 rounded-xl font-medium">
                    إلغاء
                </a>
                <button type="submit" id="submit-btn"
                        class="btn-accent flex-1 py-3 px-4 rounded-xl text-sm flex items-center justify-center gap-2">
                    <span id="btn-text">{{ $mode === 'create' ? 'إنشاء النظام' : 'حفظ التغييرات' }}</span>
                    <span id="btn-spinner" class="hidden w-5 h-5 rounded-full spinner animate-spin"></span>
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#add-redirect').on('click', function () {
                const row = `
                    <div class="redirect-row flex gap-2">
                        <input name="redirect_uris[]" type="url" dir="ltr" required
                               class="input-glass flex-1 px-3 py-2 rounded-lg font-mono text-xs"
                               placeholder="https://app.example.com/auth/callback">
                        <button type="button" class="remove-redirect icon-btn danger">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>`;
                $('#redirect-uris-list').append(row);
            });
            $(document).on('click', '.remove-redirect', function () {
                if ($('.redirect-row').length > 1) {
                    $(this).closest('.redirect-row').remove();
                } else {
                    toastr.warning('يجب أن يكون هناك رابط واحد على الأقل');
                }
            });

            $('#color-input').on('input', function () {
                const c = $(this).val();
                $('#color-text').val(c);
                $('#preview-badge').css('background', `linear-gradient(135deg, ${c}, color-mix(in srgb, ${c} 70%, black))`);
            });

            $('input[name="display_name_ar"]').on('input', function () {
                const val = $(this).val() || 'اسم النظام';
                $('#preview-name').text(val);
                $('#preview-initial').text(val.charAt(0) || 'N');
            });
            $('textarea[name="description"]').on('input', function () {
                $('#preview-desc').text($(this).val() || 'الوصف');
            });

            const $form = $('#app-form');
            const $btn = $('#submit-btn');
            const $btnText = $('#btn-text');
            const $btnSpinner = $('#btn-spinner');

            $form.on('submit', function (e) {
                e.preventDefault();
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input, textarea').removeClass('error');
                $btn.prop('disabled', true);
                $btnText.text('جارٍ الحفظ...');
                $btnSpinner.removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'), type: 'POST',
                    data: $form.serialize(), dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(function (response) {
                    toastr.success(response.message);
                    if (response.credentials) {
                        showCredentials(response.credentials.client_id, response.credentials.client_secret, response.redirect);
                    } else {
                        setTimeout(() => { window.location.href = response.redirect; }, 800);
                    }
                }).fail(function (xhr) {
                    $btn.prop('disabled', false);
                    $btnText.text('{{ $mode === 'create' ? 'إنشاء النظام' : 'حفظ التغييرات' }}');
                    $btnSpinner.addClass('hidden');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(field => {
                            const normalized = field.replace(/\.\d+$/, '');
                            const target = $form.find(`[data-error-for="${normalized}"]`);
                            if (target.length) target.text(errors[field][0]).removeClass('hidden');
                            const input = $form.find(`[name="${field}"], [name="${normalized}"]`);
                            input.addClass('error');
                        });
                        toastr.error('تحقق من الحقول الموضّحة');
                    } else {
                        toastr.error('حدث خطأ في الحفظ');
                    }
                });
            });

            function showCredentials(clientId, clientSecret, redirect) {
                Swal.fire({
                    title: 'تم إنشاء النظام بنجاح',
                    html: `
                        <p style="font-size:13px;color:rgba(255,255,255,.6);margin-bottom:1rem">احفظ هذه البيانات الآن — الـ Secret لن يظهر مرة أخرى.</p>
                        <div class="text-right" style="text-align:right">
                            <div style="margin-bottom:.75rem">
                                <label style="font-size:11px;color:rgba(255,255,255,.5)">Client ID</label>
                                <div style="display:flex;gap:.5rem;margin-top:.25rem">
                                    <input readonly value="${clientId}" dir="ltr"
                                        style="flex:1;padding:.5rem .75rem;background:rgba(8,24,48,.6);border:1.5px solid rgba(255,255,255,.1);border-radius:8px;color:white;font-family:monospace;font-size:11px">
                                    <button onclick="navigator.clipboard.writeText('${clientId}'); toastr.success('تم النسخ')"
                                        style="padding:.5rem .75rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;color:white;font-size:11px;cursor:pointer">نسخ</button>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:11px;color:#fcd34d;font-weight:600">Client Secret (خاص)</label>
                                <div style="display:flex;gap:.5rem;margin-top:.25rem">
                                    <input readonly value="${clientSecret}" dir="ltr"
                                        style="flex:1;padding:.5rem .75rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;font-size:11px">
                                    <button onclick="navigator.clipboard.writeText('${clientSecret}'); toastr.success('تم النسخ')"
                                        style="padding:.5rem .75rem;background:linear-gradient(135deg,#F97316,#FBBF24);border:none;border-radius:8px;color:white;font-size:11px;font-weight:600;cursor:pointer">نسخ</button>
                                </div>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'تم، احفظت البيانات',
                    width: 560,
                    allowOutsideClick: false,
                }).then(() => { window.location.href = redirect; });
            }
        });
    </script>
@endsection
