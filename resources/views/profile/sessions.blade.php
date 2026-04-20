@extends('profile._layout')

@section('title', 'جلساتي النشطة')

@section('content')
    <div class="card p-5 mb-5">
        <div class="flex items-start justify-between gap-3 flex-wrap">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">الجلسات النشطة</h2>
                    <p class="text-xs text-slate-500">هذه الأجهزة مسجّلة الدخول حالياً في حسابك</p>
                </div>
            </div>

            @if ($sessions->count() > 1)
                <button type="button" id="revoke-all-btn" class="btn-ghost text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5 text-rose-600 border-rose-200 hover:bg-rose-50 hover:border-rose-300">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    إنهاء الجلسات الأخرى
                </button>
            @endif
        </div>
    </div>

    @if ($sessions->isEmpty())
        <div class="card p-12 text-center">
            <p class="text-slate-500 text-sm">لا توجد جلسات نشطة.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($sessions as $s)
                @php $isCurrent = $s->id === $currentSessionId; @endphp
                <div data-session-row="{{ $s->id }}" class="card p-5 flex items-center gap-4 @if($isCurrent) ring-2 ring-emerald-300 ring-offset-2 @endif">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0
                         @if($isCurrent) bg-emerald-50 text-emerald-600 @else bg-slate-100 text-slate-600 @endif">
                        @if (str_contains(strtolower($s->user_agent ?? ''), 'iphone') || str_contains(strtolower($s->user_agent ?? ''), 'android') || str_contains(strtolower($s->user_agent ?? ''), 'mobile'))
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <p class="font-semibold text-slate-900 text-sm">
                                {{ \Illuminate\Support\Str::limit($s->user_agent, 60) ?? 'جهاز غير معروف' }}
                            </p>
                            @if ($isCurrent)
                                <span class="badge badge-success text-[10px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    هذا الجهاز
                                </span>
                            @endif
                            @if ($s->mfa_verified)
                                <span class="badge badge-info text-[10px]">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001z" clip-rule="evenodd"/></svg>
                                    MFA
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span dir="ltr">{{ $s->ip_address }}</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                آخر نشاط: {{ $s->last_activity_at?->diffForHumans() }}
                            </span>
                            <span class="flex items-center gap-1 text-slate-400">
                                تنتهي: {{ $s->expires_at?->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    @if (! $isCurrent)
                        <button type="button" data-revoke-session="{{ $s->id }}"
                                class="btn-ghost text-rose-600 border-rose-200 hover:bg-rose-50 hover:border-rose-300 text-xs font-semibold px-3 py-2 rounded-lg shrink-0">
                            إنهاء
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-revoke-session]').on('click', function () {
                const id = $(this).data('revoke-session');
                Swal.fire({
                    title: 'إنهاء هذه الجلسة؟',
                    html: 'سيتم تسجيل خروج الجهاز من النظام فوراً.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، أنهي',
                    cancelButtonText: 'إلغاء',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: `/profile/sessions/${id}/revoke`, type: 'POST', dataType: 'json' })
                        .done(d => { toastr.success(d.message); $(`[data-session-row="${id}"]`).fadeOut(300, function() { $(this).remove(); }); })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message ?? 'فشلت العملية'));
                });
            });

            $('#revoke-all-btn').on('click', function () {
                Swal.fire({
                    title: 'إنهاء جميع الجلسات الأخرى؟',
                    html: 'سيتم تسجيل خروج كل الأجهزة باستثناء هذا الجهاز.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، أنهي الكل',
                    cancelButtonText: 'إلغاء',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: `/profile/sessions/revoke-all`, type: 'POST', dataType: 'json' })
                        .done(d => { toastr.success(d.message); setTimeout(() => location.reload(), 800); })
                        .fail(() => toastr.error('فشلت العملية'));
                });
            });
        });
    </script>
@endsection
