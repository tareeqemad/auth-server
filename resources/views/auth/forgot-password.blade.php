@extends('layouts.auth')

@section('title', 'نسيت كلمة المرور')
@section('heading', 'إعادة تعيين كلمة المرور')
@section('subheading', 'أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-[13px] font-semibold text-white/70 mb-1.5">البريد الإلكتروني</label>
            <input
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                dir="ltr"
                class="input-glass w-full rounded-xl px-4 py-3 text-sm transition-all @error('email') error @enderror"
                placeholder="name@gedco.ps"
            >
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-accent w-full text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            إرسال الرابط
        </button>
    </form>

    <div class="relative py-3">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-white/[0.08]"></div>
        </div>
        <div class="relative flex justify-center text-[11px]">
            <span class="px-3 text-white/30" style="background: #0F2440">أو</span>
        </div>
    </div>

    <a href="{{ route('password.sms.phone') }}"
       class="flex items-center justify-center gap-2 w-full rounded-xl py-3.5 text-sm font-semibold transition"
       style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.9);"
       onmouseover="this.style.background='rgba(255,255,255,.08)'"
       onmouseout="this.style.background='rgba(255,255,255,.05)'">
        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        استعادة عبر SMS
    </a>

    <div class="mt-5 text-center">
        <a href="{{ route('login') }}"
           class="text-xs font-semibold text-white/40 hover:text-white/70 inline-flex items-center gap-1">
            <svg class="w-3 h-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            العودة لتسجيل الدخول
        </a>
    </div>
@endsection
