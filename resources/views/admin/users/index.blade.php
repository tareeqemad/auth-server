@extends('layouts.admin')

@section('title', 'المستخدمون')

@section('breadcrumbs')
    <span class="bc-current">المستخدمون</span>
@endsection

@section('breadcrumb_actions')
    <a href="{{ route('admin.users.create') }}" class="btn-accent text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1.5 rounded-lg inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">مستخدم جديد</span>
        <span class="sm:hidden">جديد</span>
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">إجمالي المستخدمين</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">نشطون</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">معطّلون</p>
                    <p class="text-3xl font-bold text-rose-600">{{ $stats['inactive'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <form method="GET" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-sm">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ $search }}" placeholder="بحث بالاسم أو البريد أو الهاتف..."
                           class="input-glass w-full pr-9 pl-3 py-2 rounded-lg text-sm">
                </div>
                <select name="status" class="input-glass px-3 py-2 rounded-lg text-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected($status === 'active')>نشط</option>
                    <option value="inactive" @selected($status === 'inactive')>معطّل</option>
                </select>
                <button type="submit" class="btn-ghost text-sm px-4 py-2 rounded-lg">فلترة</button>
            </form>
        </div>

        @if ($users->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-slate-900 font-medium">لا يوجد مستخدمون</p>
                <p class="text-sm text-slate-500 mt-1">ابدأ بإضافة أول مستخدم.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الهاتف</th>
                            <th>الأنظمة</th>
                            <th>الحالة</th>
                            <th>آخر دخول</th>
                            <th class="!text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr data-user-row="{{ $u->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow"
                                             style="background: linear-gradient(135deg, #F97316, #FBBF24);">
                                            {{ mb_substr($u->full_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $u->full_name }}</p>
                                            <p class="text-xs text-slate-500 truncate" dir="ltr">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-600 font-mono" dir="ltr">{{ $u->phone ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $u->system_links_count }}</span>
                                </td>
                                <td>
                                    @if ($u->is_active)
                                        <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> نشط</span>
                                    @else
                                        <span class="badge badge-danger"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> معطّل</span>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="icon-btn" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button type="button" data-action="reset-password" data-id="{{ $u->id }}" data-name="{{ $u->full_name }}" class="icon-btn warning" title="إعادة تعيين كلمة المرور">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </button>
                                        <button type="button" data-action="toggle-active" data-id="{{ $u->id }}" class="icon-btn {{ $u->is_active ? 'warning' : '' }}" title="{{ $u->is_active ? 'إيقاف' : 'تفعيل' }}">
                                            @if ($u->is_active)
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            @endif
                                        </button>
                                        <button type="button" data-action="delete" data-id="{{ $u->id }}" data-name="{{ $u->full_name }}" class="icon-btn danger" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">{{ $users->links() }}</div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-action="toggle-active"]').on('click', function () {
                const id = $(this).data('id');
                $.ajax({ url: `/admin/users/${id}/toggle-active`, type: 'POST', dataType: 'json' })
                    .done(d => { toastr.success(d.message); setTimeout(() => location.reload(), 700); })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message ?? 'فشلت العملية'));
            });

            $('[data-action="reset-password"]').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'إعادة تعيين كلمة المرور؟',
                    html: `سيتم توليد كلمة مرور جديدة للمستخدم <strong>${name}</strong>.`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، ولّد',
                    cancelButtonText: 'إلغاء',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: `/admin/users/${id}/reset-password`, type: 'POST', dataType: 'json' })
                        .done(d => {
                            Swal.fire({
                                title: d.message,
                                html: `<p style="color:rgba(255,255,255,.7);margin:1rem 0">كلمة المرور الجديدة (أعطها للمستخدم):</p>
                                       <input readonly value="${d.password}" dir="ltr"
                                           style="width:100%;padding:.5rem .75rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;font-size:14px;text-align:center">
                                       <button onclick="navigator.clipboard.writeText('${d.password}'); toastr.success('تم النسخ')"
                                           style="margin-top:.75rem;padding:.5rem 1rem;background:linear-gradient(135deg,#F97316,#FBBF24);border:none;border-radius:8px;color:white;font-size:12px;font-weight:600;cursor:pointer">نسخ</button>`,
                                icon: 'success',
                            });
                        })
                        .fail(() => toastr.error('فشل توليد كلمة المرور'));
                });
            });

            $('[data-action="delete"]').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'حذف المستخدم؟',
                    html: `هل أنت متأكد من حذف <strong>${name}</strong>؟`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: `/admin/users/${id}`, type: 'DELETE', dataType: 'json' })
                        .done(d => { toastr.success(d.message); $(`[data-user-row="${id}"]`).fadeOut(300, function() { $(this).remove(); }); })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message ?? 'فشل الحذف'));
                });
            });
        });
    </script>
@endsection
