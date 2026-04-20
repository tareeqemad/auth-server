@extends('layouts.admin')

@section('title', 'الجلسات النشطة')

@section('breadcrumbs')
    <span class="bc-current">الجلسات النشطة</span>
@endsection

@section('content')
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">نشطة الآن</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">منتهية الصلاحية</p>
                    <p class="text-3xl font-bold text-amber-600">{{ $stats['expired'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">مُنهاة يدوياً</p>
                    <p class="text-3xl font-bold text-rose-600">{{ $stats['revoked'] }}</p>
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
                    <input type="text" name="q" value="{{ $search }}" placeholder="بحث باسم المستخدم أو البريد..."
                           class="input-glass w-full pr-9 pl-3 py-2 rounded-lg text-sm">
                </div>
                <button type="submit" class="btn-ghost text-sm px-4 py-2 rounded-lg">فلترة</button>
            </form>
        </div>

        @if ($sessions->isEmpty())
            <div class="px-6 py-16 text-center text-slate-500 text-sm">لا توجد جلسات نشطة.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>IP</th>
                            <th>آخر نشاط</th>
                            <th>انتهاء الصلاحية</th>
                            <th>MFA</th>
                            <th class="!text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $s)
                            <tr data-session-row="{{ $s->id }}">
                                <td>
                                    @if ($s->user)
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm"
                                                 style="background: linear-gradient(135deg, #F97316, #FBBF24);">
                                                {{ mb_substr($s->user->full_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate">{{ $s->user->full_name }}</p>
                                                <p class="text-xs text-slate-500 truncate" dir="ltr">{{ $s->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-sm">محذوف</span>
                                    @endif
                                </td>
                                <td class="font-mono text-xs text-slate-500" dir="ltr">{{ $s->ip_address ?? '—' }}</td>
                                <td class="text-xs text-slate-500">{{ $s->last_activity_at->diffForHumans() }}</td>
                                <td class="text-xs text-slate-500">{{ $s->expires_at->diffForHumans() }}</td>
                                <td>
                                    @if ($s->mfa_verified)
                                        <span class="badge badge-success">مُفعّلة</span>
                                    @else
                                        <span class="badge badge-warning">لا</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-center">
                                        <button type="button" data-action="revoke" data-id="{{ $s->id }}" data-name="{{ $s->user?->full_name }}"
                                                class="icon-btn danger" title="إنهاء الجلسة">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">{{ $sessions->links() }}</div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-action="revoke"]').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'إنهاء الجلسة؟',
                    html: `سيُطرَد <strong>${name ?? 'المستخدم'}</strong> من النظام فوراً.`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، أنهي الجلسة',
                    cancelButtonText: 'إلغاء',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: `/admin/sessions/${id}/revoke`, type: 'POST', dataType: 'json' })
                        .done(d => { toastr.success(d.message); $(`[data-session-row="${id}"]`).fadeOut(300, function() { $(this).remove(); }); })
                        .fail(() => toastr.error('فشلت العملية'));
                });
            });
        });
    </script>
@endsection
