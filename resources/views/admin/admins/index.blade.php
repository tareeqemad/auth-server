@extends('layouts.admin')

@section('title', 'المدراء والصلاحيات')

@section('breadcrumbs')
    <span class="bc-current">المدراء والصلاحيات</span>
@endsection

@section('content')
    @php
        $roleLabels = [
            'super_admin' => ['مدير عام', 'كل الصلاحيات', 'rose'],
            'user_manager' => ['مدير مستخدمين', 'إدارة المستخدمين والجلسات', 'blue'],
            'client_manager' => ['مدير أنظمة', 'إدارة OAuth clients فقط', 'violet'],
            'viewer' => ['مشاهِد', 'قراءة فقط', 'slate'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach ($roles as $role)
            @php $info = $roleLabels[$role->name] ?? [$role->name, '', 'slate']; @endphp
            <div class="card-glass rounded-2xl p-5">
                <div class="flex items-start justify-between mb-2">
                    <span class="badge badge-{{ $info[2] === 'rose' ? 'danger' : ($info[2] === 'blue' ? 'info' : ($info[2] === 'violet' ? 'info' : 'success')) }}">
                        {{ $info[0] }}
                    </span>
                    <span class="text-2xl font-bold text-slate-900">{{ $role->users_count }}</span>
                </div>
                <p class="text-xs text-slate-500">{{ $info[1] }}</p>
            </div>
        @endforeach
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">قائمة المدراء</h2>
                <p class="text-xs text-slate-500 mt-0.5">المستخدمون الذين يقدرون على الوصول للوحة التحكم</p>
            </div>
            <p class="text-xs text-slate-500">لإضافة مدير جديد، استخدم: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">php artisan sso:create-admin</code></p>
        </div>

        @if ($admins->isEmpty())
            <div class="px-6 py-16 text-center text-slate-500 text-sm">لا يوجد مدراء بعد.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>المدير</th>
                            <th>الدور</th>
                            <th>آخر دخول</th>
                            <th>الحالة</th>
                            <th class="!text-center">تغيير الصلاحية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admins as $admin)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm"
                                             style="background: linear-gradient(135deg, #F97316, #FBBF24);">
                                            {{ mb_substr($admin->full_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $admin->full_name }}</p>
                                            <p class="text-xs text-slate-500 truncate" dir="ltr">{{ $admin->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach ($admin->roles as $r)
                                        @php $info = $roleLabels[$r->name] ?? [$r->name, '', 'slate']; @endphp
                                        <span class="badge badge-{{ $info[2] === 'rose' ? 'danger' : ($info[2] === 'blue' ? 'info' : ($info[2] === 'violet' ? 'info' : 'success')) }}">
                                            {{ $info[0] }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-xs text-slate-500">{{ $admin->last_login_at?->diffForHumans() ?? '—' }}</td>
                                <td>
                                    @if ($admin->is_active)
                                        <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> نشط</span>
                                    @else
                                        <span class="badge badge-danger">معطّل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-center">
                                        <select data-change-role data-id="{{ $admin->id }}"
                                                class="input-glass px-2 py-1 rounded-lg text-xs"
                                                @if($admin->id === auth()->id()) disabled title="لا يمكنك تغيير دورك" @endif>
                                            <option value="">— تغيير الدور —</option>
                                            @foreach (\App\Models\User::ADMIN_ROLES as $r)
                                                @php $info = $roleLabels[$r] ?? [$r]; @endphp
                                                <option value="{{ $r }}" @selected($admin->roles->contains('name', $r))>{{ $info[0] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">{{ $admins->links() }}</div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-change-role]').on('change', function () {
                const id = $(this).data('id');
                const role = $(this).val();
                if (!role) return;
                $.ajax({
                    url: `/admin/admins/${id}/assign-role`, type: 'POST',
                    data: { role: role }, dataType: 'json',
                }).done(r => { toastr.success(r.message); setTimeout(() => location.reload(), 700); })
                  .fail(() => toastr.error('فشل التحديث'));
            });
        });
    </script>
@endsection
