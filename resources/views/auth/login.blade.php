@extends('layouts.auth')

@section('title', 'تسجيل الدخول')
@section('heading', 'تسجيل الدخول إلى حسابك')
@section('subheading', 'أدخل بياناتك للمتابعة إلى النظام الموحّد')

@section('content')
    <div id="login-alert" class="hidden items-start gap-2.5 p-3.5 rounded-xl mb-5"
         style="background: rgba(239,68,68,.12); border: 1px solid rgba(248,113,113,.25);">
        <svg class="w-5 h-5 mt-0.5 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span id="login-alert-text" class="text-sm text-rose-300 leading-relaxed"></span>
    </div>

    <div id="caps-warning" class="hidden items-center gap-2 p-2.5 rounded-lg mb-4"
         style="background: rgba(245,158,11,.1); border: 1px solid rgba(251,191,36,.25);">
        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
        </svg>
        <span class="text-xs text-amber-300">زر Caps Lock مفعّل</span>
    </div>

    <form id="login-form" method="POST" action="{{ route('login.authenticate') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label class="block text-[12px] font-semibold text-white/60 mb-1.5 tracking-wide">
                البريد الإلكتروني
            </label>
            <div class="relative group">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-white/30 group-focus-within:text-amber-400 transition pointer-events-none">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    dir="ltr"
                    class="input-glass w-full rounded-xl pr-11 pl-4 py-3.5 text-sm"
                    placeholder="name@gedco.ps"
                >
            </div>
            <p data-error-for="email" class="hidden mt-1.5 text-xs text-rose-400 flex items-start gap-1"></p>
        </div>

        <div>
            <label class="flex items-center justify-between text-[12px] font-semibold text-white/60 mb-1.5 tracking-wide">
                <span>كلمة المرور</span>
                <a href="{{ route('password.request') }}" class="text-[11px] font-medium text-amber-400/70 hover:text-amber-400 transition">
                    نسيت كلمة المرور؟
                </a>
            </label>
            <div class="relative group">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-white/30 group-focus-within:text-amber-400 transition pointer-events-none">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <button type="button" id="toggle-password"
                        class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg flex items-center justify-center text-white/65 hover:text-amber-400 transition"
                        style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08);"
                        title="إظهار/إخفاء كلمة المرور">
                    <svg id="eye-show" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg id="eye-hide" class="w-[18px] h-[18px] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    dir="ltr"
                    class="input-glass w-full rounded-xl pr-11 pl-12 py-3.5 text-sm"
                    placeholder="••••••••"
                >
            </div>
            <p data-error-for="password" class="hidden mt-1.5 text-xs text-rose-400"></p>
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="custom-check">
                <input type="checkbox" name="remember" value="1">
                <span class="check-box"></span>
                <span class="label-text">احتفظ بدخولي لمدة ٣٠ يوماً</span>
            </label>
        </div>

        <button id="submit-btn" type="submit"
                class="btn-accent has-ripple w-full text-white rounded-xl py-3.5 font-bold text-[15px] flex items-center justify-center gap-2.5 mt-2">
            <svg id="btn-icon" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span id="btn-spinner" class="hidden w-[18px] h-[18px] rounded-full spinner animate-spin"></span>
            <span id="btn-text">تسجيل الدخول</span>
            <svg class="w-4 h-4 rtl:rotate-180 opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Button ripple effect
            $(document).on('click', '.has-ripple', function (e) {
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = (e.clientX ?? rect.left + rect.width/2) - rect.left - size/2;
                const y = (e.clientY ?? rect.top + rect.height/2) - rect.top - size/2;
                const $ripple = $('<span class="ripple-wave"></span>').css({
                    width: size + 'px', height: size + 'px',
                    left: x + 'px', top: y + 'px',
                });
                $(this).append($ripple);
                setTimeout(() => $ripple.remove(), 600);
            });

            const $form = $('#login-form');
            const $btn = $('#submit-btn');
            const $btnText = $('#btn-text');
            const $btnIcon = $('#btn-icon');
            const $btnSpinner = $('#btn-spinner');
            const $alert = $('#login-alert');
            const $alertText = $('#login-alert-text');
            const $capsWarning = $('#caps-warning');

            $('#toggle-password').on('click', function () {
                const $pw = $('#password');
                const isHidden = $pw.attr('type') === 'password';
                $pw.attr('type', isHidden ? 'text' : 'password').focus();
                $('#eye-show').toggleClass('hidden', isHidden);
                $('#eye-hide').toggleClass('hidden', !isHidden);
            });

            $('#password, #email').on('keydown keyup', function (e) {
                const ev = e.originalEvent;
                if (ev.getModifierState) {
                    const capsOn = ev.getModifierState('CapsLock');
                    $capsWarning.toggleClass('hidden', !capsOn).toggleClass('flex', capsOn);
                }
            });

            function clearErrors() {
                $alert.addClass('hidden').removeClass('flex');
                $form.find('[data-error-for]').addClass('hidden').text('');
                $form.find('input').removeClass('error');
            }

            function showFieldError(field, message) {
                $form.find(`[data-error-for="${field}"]`).text(message).removeClass('hidden');
                $form.find(`[name="${field}"]`).addClass('error');
            }

            function showAlert(message) {
                $alertText.text(message);
                $alert.removeClass('hidden').addClass('flex');
            }

            function lock() {
                $btn.prop('disabled', true);
                $btnText.text('جاري التحقق...');
                $btnIcon.addClass('hidden');
                $btnSpinner.removeClass('hidden');
            }

            function unlock() {
                $btn.prop('disabled', false);
                $btnText.text('تسجيل الدخول');
                $btnIcon.removeClass('hidden');
                $btnSpinner.addClass('hidden');
            }

            function success() {
                $btnSpinner.addClass('hidden');
                $btnIcon.removeClass('hidden').html('<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>');
                $btn.css('background', 'linear-gradient(135deg, #059669 0%, #10b981 100%)');
                $btnText.text('تم بنجاح');
            }

            $form.on('submit', function (e) {
                e.preventDefault();
                clearErrors();
                lock();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                }).done(function (response) {
                    if (response.success && response.redirect) {
                        success();
                        toastr.success('تم تسجيل الدخول بنجاح');
                        setTimeout(() => { window.location.href = response.redirect; }, 600);
                        return;
                    }
                    unlock();
                }).fail(function (xhr) {
                    unlock();

                    if (xhr.status === 422) {
                        const data = xhr.responseJSON || {};
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const msg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                showFieldError(field, msg);
                            });
                        }
                        if (data.message) {
                            showAlert(data.message);
                        }
                        $form.addClass('animate-shake');
                        setTimeout(() => $form.removeClass('animate-shake'), 400);
                    } else if (xhr.status === 429) {
                        showAlert('عدد كبير من المحاولات. انتظر دقيقة ثم حاول مجدداً.');
                    } else {
                        showAlert('حدث خطأ في الاتصال. تحقق من اتصالك بالإنترنت.');
                    }
                });
            });
        });
    </script>

    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        .animate-shake { animation: shake .4s cubic-bezier(.36,.07,.19,.97); }
    </style>
@endsection
