@extends('layouts.auth')

@section('title', 'نسيت كلمة المرور')
@section('heading', 'إعادة تعيين كلمة المرور')
@section('subheading', 'سنرسل لك رمز تحقق عبر رسالة SMS')

@section('content')
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
             style="background: rgba(251,191,36,.12); border: 1.5px solid rgba(251,191,36,.3);">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="#FBBF24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <p class="text-sm text-white/60">
            أدخل رقم جوالك المسجّل في النظام وسيصلك رمز التحقق.
        </p>
    </div>

    <a href="{{ route('password.sms.phone') }}"
       class="btn-accent w-full text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        استعادة عبر SMS
    </a>

    <div class="mt-5 text-center space-y-2">
        <p class="text-[11px] text-white/40">
            لا يوجد رقم جوال مسجّل؟ تواصل مع <a href="mailto:{{ $brand['support_email'] ?? 'support@gedco.ps' }}" class="text-amber-400/80 hover:text-amber-400">الدعم الفني</a>
        </p>
        <a href="{{ route('login') }}"
           class="text-xs font-semibold text-white/40 hover:text-white/70 inline-flex items-center gap-1">
            <svg class="w-3 h-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            العودة لتسجيل الدخول
        </a>
    </div>
@endsection
