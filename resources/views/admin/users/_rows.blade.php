@foreach ($users as $u)
    <tr data-user-row="{{ $u->id }}">
        <td>
            @if ($u->employee_number)
                <span class="badge badge-info font-mono" dir="ltr">#{{ $u->employee_number }}</span>
            @else
                <span class="text-xs text-slate-400">—</span>
            @endif
        </td>
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
        <td class="text-xs text-slate-600 font-mono" dir="ltr">
            @if ($u->national_id)
                {{ $u->national_id }}
            @elseif ($u->needs_id_linking)
                <span class="text-amber-600" title="بحاجة ربط هوية">⚠️</span>
            @else
                —
            @endif
        </td>
        <td>
            <span class="badge badge-info">{{ $u->system_links_count }}</span>
        </td>
        <td>
            <div class="flex flex-col gap-1 items-start">
                @if ($u->is_active)
                    <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> نشط</span>
                @else
                    <span class="badge badge-danger"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> معطّل</span>
                @endif

                @if ($u->isLocked())
                    @if ($u->locked_by_admin_id)
                        <span class="badge badge-danger" title="{{ $u->locked_reason }}">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            محظور (إداري)
                        </span>
                    @else
                        <span class="badge badge-warning" title="حتى {{ $u->locked_until->format('Y-m-d H:i') }}">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            موقوف مؤقتاً
                        </span>
                    @endif
                @elseif ($u->failed_login_attempts > 0)
                    <span class="badge badge-warning text-[10px]" title="محاولات فاشلة مؤخراً">
                        محاولات: {{ $u->failed_login_attempts }}
                    </span>
                @endif
            </div>
        </td>
        <td class="text-xs text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
        <td>
            <div class="flex items-center justify-center gap-1">
                <a href="{{ route('admin.users.show', $u) }}" class="icon-btn" title="استعلام" style="color:#0284c7">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="{{ route('admin.users.edit', $u) }}" class="icon-btn" title="تعديل">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @if ($u->isLocked() || $u->failed_login_attempts > 0)
                    <button type="button" data-action="unlock" data-id="{{ $u->id }}" data-name="{{ $u->full_name }}" class="icon-btn" style="color:#10b981" title="فك الحظر">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </button>
                @else
                    <button type="button" data-action="lock" data-id="{{ $u->id }}" data-name="{{ $u->full_name }}" class="icon-btn danger" title="حظر الحساب">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </button>
                @endif
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
