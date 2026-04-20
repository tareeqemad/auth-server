@extends('layouts.admin')

@section('title', 'الأنظمة')

@section('breadcrumbs')
    <span class="bc-current">الأنظمة</span>
@endsection

@section('breadcrumb_actions')
    <a href="{{ route('admin.applications.create') }}" class="btn-accent text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1.5 rounded-lg inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">نظام جديد</span>
        <span class="sm:hidden">جديد</span>
    </a>
@endsection

@section('content')
    @php
        $totalCount = \App\Models\Application::whereNull('deleted_at')->count();
        $activeCount = \App\Models\Application::whereNull('deleted_at')->where('revoked', false)->count();
        $revokedCount = $totalCount - $activeCount;
    @endphp

    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">إجمالي الأنظمة</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $totalCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 text-violet-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">نشطة</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $activeCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">معطّلة</p>
                    <p class="text-3xl font-bold text-rose-600">{{ $revokedCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap gap-3 items-center justify-between">
            <form method="GET" class="flex gap-2 flex-1 min-w-[280px]">
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ $search }}" placeholder="بحث..."
                           class="input-glass w-full pr-9 pl-3 py-2 rounded-lg text-sm">
                </div>
                <select name="status" class="input-glass px-3 py-2 rounded-lg text-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected($status === 'active')>نشطة</option>
                    <option value="revoked" @selected($status === 'revoked')>معطّلة</option>
                </select>
                <button type="submit" class="btn-ghost text-sm px-4 py-2 rounded-lg">فلترة</button>
            </form>

            <a href="{{ route('admin.applications.create') }}"
               class="btn-accent text-sm font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة نظام جديد
            </a>
        </div>

        @if ($applications->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="inline-flex w-16 h-16 rounded-full items-center justify-center mb-4 bg-slate-100 border border-slate-200">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="text-slate-900 font-medium">لا توجد أنظمة مسجّلة</p>
                <p class="text-sm text-slate-500 mt-1">ابدأ بإضافة أول نظام لربطه بالـ SSO.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>النظام</th>
                            <th>المعرّف</th>
                            <th>Redirect URIs</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th class="!text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $app)
                            <tr data-app-row="{{ $app->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg"
                                             style="background: linear-gradient(135deg, {{ $app->color }}, color-mix(in srgb, {{ $app->color }} 70%, black))">
                                            {{ $app->initial() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $app->displayName() }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ $app->description ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-xs font-mono text-slate-600 px-2 py-1 rounded bg-slate-100 border border-slate-200" dir="ltr">
                                        {{ $app->slug ?? '—' }}
                                    </code>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        {{ count($app->redirect_uris ?? []) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($app->revoked)
                                        <span class="badge badge-danger">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> معطّل
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> نشط
                                        </span>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-500">{{ $app->created_at?->diffForHumans() }}</td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.applications.integration', $app) }}" class="icon-btn" title="دليل التكامل للمطوّرين">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        </a>
                                        <a href="{{ route('admin.applications.edit', $app) }}" class="icon-btn" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button type="button" data-action="rotate-secret" data-id="{{ $app->id }}" class="icon-btn warning" title="توليد مفتاح جديد">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </button>
                                        <button type="button" data-action="toggle-revoke" data-id="{{ $app->id }}"
                                                class="icon-btn {{ $app->revoked ? '' : 'warning' }}" title="{{ $app->revoked ? 'تفعيل' : 'إيقاف' }}">
                                            @if ($app->revoked)
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @endif
                                        </button>
                                        <button type="button" data-action="delete" data-id="{{ $app->id }}" data-name="{{ $app->displayName() }}" class="icon-btn danger" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-action="rotate-secret"]').on('click', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'توليد مفتاح سري جديد؟',
                    html: 'سيتوقّف المفتاح القديم فوراً.<br><strong style="color:#fcd34d">تأكّد من تحديث المفتاح في النظام المعني.</strong>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، ولّد مفتاحاً جديداً',
                    cancelButtonText: 'إلغاء',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: `/admin/applications/${id}/rotate-secret`, type: 'POST', dataType: 'json' })
                        .done((data) => showCredentials(data.client_id, data.client_secret, data.message))
                        .fail(() => toastr.error('فشل توليد المفتاح'));
                });
            });

            $('[data-action="toggle-revoke"]').on('click', function () {
                const id = $(this).data('id');
                $.ajax({ url: `/admin/applications/${id}/toggle-revoke`, type: 'POST', dataType: 'json' })
                    .done((data) => { toastr.success(data.message); setTimeout(() => location.reload(), 800); })
                    .fail(() => toastr.error('فشلت العملية'));
            });

            $('[data-action="delete"]').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'حذف النظام؟',
                    html: `هل أنت متأكد من حذف <strong style="color:#fca5a5">${name}</strong>؟<br><span style="color:rgba(255,255,255,.6)">سيمنع مستخدموه من الدخول.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: `/admin/applications/${id}`, type: 'DELETE', dataType: 'json' })
                        .done((data) => {
                            toastr.success(data.message);
                            $(`[data-app-row="${id}"]`).fadeOut(300, function () { $(this).remove(); });
                        })
                        .fail(() => toastr.error('فشل الحذف'));
                });
            });

            function showCredentials(clientId, clientSecret, msg) {
                Swal.fire({
                    title: msg,
                    html: `
                        <div class="text-right text-sm space-y-3 mt-4">
                            <div>
                                <label style="font-size:11px;color:rgba(255,255,255,.5)">Client ID</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <input readonly value="${clientId}" dir="ltr"
                                        style="width:100%;padding:.5rem .75rem;background:rgba(8,24,48,.6);border:1.5px solid rgba(255,255,255,.1);border-radius:8px;color:white;font-family:monospace;font-size:11px">
                                    <button onclick="navigator.clipboard.writeText('${clientId}'); toastr.success('تم النسخ')"
                                        style="padding:.5rem .75rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;color:white;font-size:11px">نسخ</button>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:11px;color:#fcd34d;font-weight:600">Client Secret (خاص)</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <input readonly value="${clientSecret}" dir="ltr"
                                        style="width:100%;padding:.5rem .75rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;font-size:11px">
                                    <button onclick="navigator.clipboard.writeText('${clientSecret}'); toastr.success('تم النسخ')"
                                        style="padding:.5rem .75rem;background:linear-gradient(135deg,#F97316,#FBBF24);border-radius:8px;color:white;font-size:11px;font-weight:600">نسخ</button>
                                </div>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'تم، حفظت المفتاح',
                    width: 560,
                });
            }
        });
    </script>
@endsection
