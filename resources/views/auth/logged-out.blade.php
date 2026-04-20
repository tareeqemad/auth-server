@extends('layouts.auth')

@section('title', 'تم تسجيل الخروج')

@section('content')
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6"
             style="background: rgba(16, 185, 129, 0.12); border: 1.5px solid rgba(16, 185, 129, 0.3);">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">تم تسجيل الخروج بنجاح</h1>
        <p class="text-sm text-white/60 mb-8">
            أنهينا جلستك على جميع الأنظمة المرتبطة.
        </p>

        <a href="{{ $return_url }}"
           class="inline-flex items-center justify-center w-full px-6 py-3 rounded-xl font-semibold text-white shadow-lg"
           style="background: linear-gradient(135deg, #F97316, #FBBF24);">
            العودة لتسجيل الدخول
        </a>
    </div>
@endsection
