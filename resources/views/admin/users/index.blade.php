@extends('layouts.admin')

@section('title', 'المستخدمون')

@section('breadcrumbs')
    <span class="bc-current">المستخدمون</span>
@endsection

@section('breadcrumb_actions')
    <a href="{{ route('admin.users.export', request()->query()) }}" class="btn-ghost text-xs sm:text-sm font-medium px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span class="hidden sm:inline">Export CSV</span>
    </a>
    <a href="{{ route('admin.users.create') }}" class="btn-accent text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1.5 rounded-lg inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">مستخدم جديد</span>
        <span class="sm:hidden">جديد</span>
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
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
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500 mb-1">محظورون</p>
                    <p class="text-3xl font-bold text-amber-600">{{ $stats['locked'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
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
                    <input type="text" name="q" value="{{ $search }}" placeholder="بحث بالاسم / البريد / الهاتف / الرقم الوظيفي / الهوية..."
                           class="input-glass w-full pr-9 pl-3 py-2 rounded-lg text-sm">
                </div>
                <select name="status" class="input-glass px-3 py-2 rounded-lg text-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected($status === 'active')>نشط</option>
                    <option value="inactive" @selected($status === 'inactive')>معطّل</option>
                    <option value="locked" @selected($status === 'locked')>محظور</option>
                </select>
                <button type="submit" class="btn-ghost text-sm px-4 py-2 rounded-lg">فلترة</button>
            </form>
        </div>

        <div id="empty-state" class="px-6 py-16 text-center {{ $users->isEmpty() ? '' : 'hidden' }}">
            <p class="text-slate-900 font-medium">لا يوجد مستخدمون</p>
            <p class="text-sm text-slate-500 mt-1">ابدأ بإضافة أول مستخدم.</p>
        </div>

        <div id="table-wrapper" class="overflow-x-auto {{ $users->isEmpty() ? 'hidden' : '' }}">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>الرقم الوظيفي</th>
                        <th>المستخدم</th>
                        <th>الهاتف</th>
                        <th>الهوية</th>
                        <th>الأنظمة</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th class="!text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    @include('admin.users._rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        <div id="loader-bar" class="hidden px-6 py-5 border-t border-slate-200 text-center">
            <div class="inline-flex items-center gap-2 text-sm text-slate-500">
                <svg class="animate-spin w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span>جاري تحميل المزيد...</span>
            </div>
        </div>

        <div id="counter-bar" class="px-6 py-3 border-t border-slate-200 text-xs text-slate-500 flex items-center justify-between">
            <span>
                عرض <strong id="cnt-loaded" class="text-slate-900">{{ $users->count() }}</strong>
                من <strong id="cnt-total" class="text-slate-900">{{ $users->total() }}</strong>
            </span>
            <span id="cnt-done" class="text-emerald-600 font-semibold {{ $users->hasMorePages() ? 'hidden' : '' }}">✓ اكتملت القائمة</span>
        </div>
    </div>

    {{-- Floating scroll-to-top button — pinned to viewport far-left --}}
    <button type="button" id="scroll-top-btn" aria-label="العودة للأعلى" dir="ltr"
            style="position: fixed !important;
                   bottom: 24px !important;
                   left: 20px !important;
                   right: auto !important;
                   top: auto !important;
                   z-index: 9999;
                   width: 48px;
                   height: 48px;
                   border-radius: 9999px;
                   display: none;
                   align-items: center;
                   justify-content: center;
                   background: linear-gradient(135deg, #F97316, #FBBF24);
                   color: white;
                   border: 2px solid rgba(255,255,255,.3);
                   box-shadow: 0 10px 25px rgba(251,146,60,.4);
                   cursor: pointer;
                   transition: transform .15s ease;">
        <svg style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========= State =========
            var state = {
                currentPage: {{ $users->currentPage() }},
                lastPage: {{ $users->lastPage() }},
                total: {{ $users->total() }},
                hasMore: {!! $users->hasMorePages() ? 'true' : 'false' !!},
                loading: false
            };

            var $tbody = $('#users-tbody');
            var $loader = $('#loader-bar');
            var $cntLoaded = $('#cnt-loaded');
            var $cntTotal = $('#cnt-total');
            var $cntDone = $('#cnt-done');

            function buildQuery(page) {
                var params = new URLSearchParams(window.location.search);
                params.set('page', page);
                return params.toString();
            }

            function updateCounter() {
                $cntLoaded.text($tbody.find('tr').length);
                $cntTotal.text(state.total);
                if (!state.hasMore) {
                    $cntDone.removeClass('hidden');
                } else {
                    $cntDone.addClass('hidden');
                }
            }

            // ========= Infinite scroll =========
            function loadNextPage() {
                if (state.loading || !state.hasMore) return;

                state.loading = true;
                $loader.removeClass('hidden');

                $.ajax({
                    url: '{{ route('admin.users.index') }}',
                    type: 'GET',
                    data: buildQuery(state.currentPage + 1),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    dataType: 'json'
                })
                    .done(function (res) {
                        if (res && res.html) {
                            $tbody.append(res.html);
                            state.currentPage = res.current_page;
                            state.hasMore = res.has_more;
                            updateCounter();
                        }
                    })
                    .fail(function () {
                        toastr.error('فشل تحميل المزيد من المستخدمين');
                    })
                    .always(function () {
                        state.loading = false;
                        $loader.addClass('hidden');
                    });
            }

            // Scroll detector — near bottom triggers load
            var scrollTimer = null;
            $(window).on('scroll', function () {
                if (scrollTimer) return;
                scrollTimer = setTimeout(function () {
                    scrollTimer = null;
                    var scrollBottom = $(window).scrollTop() + $(window).height();
                    var pageBottom = $(document).height();
                    if (scrollBottom >= pageBottom - 400) {
                        loadNextPage();
                    }
                }, 120);
            });

            // ========= Row actions (event delegation) =========
            $tbody.on('click', '[data-action="toggle-active"]', function () {
                var $btn = $(this);
                var id = $btn.data('id');
                $.ajax({ url: '/admin/users/' + id + '/toggle-active', type: 'POST', dataType: 'json' })
                    .done(function (d) {
                        toastr.success(d.message);
                        setTimeout(function () { location.reload(); }, 700);
                    })
                    .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
            });

            $tbody.on('click', '[data-action="unlock"]', function () {
                var $btn = $(this);
                var id = $btn.data('id');
                var name = $btn.data('name');
                Swal.fire({
                    title: 'فك الحظر عن المستخدم؟',
                    html: 'سيتم فك الحظر والسماح لـ <strong>' + name + '</strong> بالدخول فوراً.',
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: 'نعم، فك الحظر',
                    cancelButtonText: 'إلغاء'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/unlock', type: 'POST', dataType: 'json' })
                        .done(function (d) {
                            toastr.success(d.message);
                            setTimeout(function () { location.reload(); }, 700);
                        })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
                });
            });

            $tbody.on('click', '[data-action="lock"]', function () {
                var $btn = $(this);
                var id = $btn.data('id');
                var name = $btn.data('name');
                Swal.fire({
                    title: 'حظر الحساب',
                    html: '<p style="margin-bottom:1rem">حظر <strong>' + name + '</strong> عن تسجيل الدخول.</p>' +
                          '<label style="display:block;text-align:right;font-size:12px;margin-bottom:.25rem;color:rgba(255,255,255,.7)">المدة (دقائق) — اتركها فارغة للحظر الدائم:</label>' +
                          '<input id="swal-minutes" type="number" min="1" placeholder="مثلاً: 60" style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;color:white;font-family:monospace;margin-bottom:.75rem">' +
                          '<label style="display:block;text-align:right;font-size:12px;margin-bottom:.25rem;color:rgba(255,255,255,.7)">السبب (اختياري):</label>' +
                          '<input id="swal-reason" type="text" maxlength="255" placeholder="مثلاً: شكوى أمنية" style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;color:white">',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، احظر',
                    cancelButtonText: 'إلغاء',
                    preConfirm: function () {
                        return {
                            minutes: $('#swal-minutes').val() || null,
                            reason: $('#swal-reason').val() || null
                        };
                    }
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/lock', type: 'POST', dataType: 'json', data: r.value })
                        .done(function (d) {
                            toastr.success(d.message);
                            setTimeout(function () { location.reload(); }, 700);
                        })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشلت العملية'); });
                });
            });

            $tbody.on('click', '[data-action="reset-password"]', function () {
                var $btn = $(this);
                var id = $btn.data('id');
                var name = $btn.data('name');
                Swal.fire({
                    title: 'إعادة تعيين كلمة المرور؟',
                    html: 'سيتم توليد كلمة مرور جديدة للمستخدم <strong>' + name + '</strong>.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، ولّد',
                    cancelButtonText: 'إلغاء'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id + '/reset-password', type: 'POST', dataType: 'json' })
                        .done(function (d) {
                            Swal.fire({
                                title: d.message,
                                html: '<p style="color:rgba(255,255,255,.7);margin:1rem 0">كلمة المرور الجديدة (أعطها للمستخدم):</p>' +
                                      '<input readonly value="' + d.password + '" dir="ltr" style="width:100%;padding:.5rem .75rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;font-size:14px;text-align:center">' +
                                      '<button onclick="navigator.clipboard.writeText(\'' + d.password + '\'); toastr.success(\'تم النسخ\')" style="margin-top:.75rem;padding:.5rem 1rem;background:linear-gradient(135deg,#F97316,#FBBF24);border:none;border-radius:8px;color:white;font-size:12px;font-weight:600;cursor:pointer">نسخ</button>',
                                icon: 'success'
                            });
                        })
                        .fail(function () { toastr.error('فشل توليد كلمة المرور'); });
                });
            });

            $tbody.on('click', '[data-action="delete"]', function () {
                var $btn = $(this);
                var id = $btn.data('id');
                var name = $btn.data('name');
                Swal.fire({
                    title: 'حذف المستخدم؟',
                    html: 'هل أنت متأكد من حذف <strong>' + name + '</strong>؟',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: '/admin/users/' + id, type: 'DELETE', dataType: 'json' })
                        .done(function (d) {
                            toastr.success(d.message);
                            $('[data-user-row="' + id + '"]').fadeOut(300, function () {
                                $(this).remove();
                                state.total = Math.max(0, state.total - 1);
                                updateCounter();
                            });
                        })
                        .fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'فشل الحذف'); });
                });
            });

            // ========= Scroll-to-top button =========
            var $scrollTopBtn = $('#scroll-top-btn');
            $(window).on('scroll', function () {
                if ($(window).scrollTop() > 400) {
                    $scrollTopBtn.css('display', 'flex');
                } else {
                    $scrollTopBtn.css('display', 'none');
                }
            });

            $scrollTopBtn.on('click', function () {
                $('html, body').animate({ scrollTop: 0 }, 500);
            }).on('mouseenter', function () {
                $(this).css('transform', 'scale(1.1)');
            }).on('mouseleave', function () {
                $(this).css('transform', 'scale(1)');
            });

            // Initial counter sync
            updateCounter();
        });
    </script>
@endsection
