@extends('layouts.auth')

@section('title', 'تعيين كلمة مرور جديدة')
@section('heading', 'تعيين كلمة مرور جديدة')
@section('subheading', 'اختر كلمة مرور قوية لحماية حسابك')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-[13px] font-semibold text-white/70 mb-1.5">البريد الإلكتروني</label>
            <input
                name="email"
                type="email"
                value="{{ old('email', $email) }}"
                required
                readonly
                dir="ltr"
                class="input-glass w-full rounded-xl px-4 py-3 text-sm opacity-75 cursor-not-allowed"
            >
        </div>

        <div>
            <label class="block text-[13px] font-semibold text-white/70 mb-1.5">كلمة المرور الجديدة</label>
            <input
                name="password"
                type="password"
                required
                autofocus
                autocomplete="new-password"
                dir="ltr"
                class="input-glass w-full rounded-xl px-4 py-3 text-sm @error('password') error @enderror"
                placeholder="٨ أحرف على الأقل"
            >
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-[13px] font-semibold text-white/70 mb-1.5">تأكيد كلمة المرور</label>
            <input
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                dir="ltr"
                class="input-glass w-full rounded-xl px-4 py-3 text-sm"
            >
        </div>

        <button type="submit" class="btn-accent w-full text-white rounded-xl py-3.5 font-bold text-sm">
            تحديث كلمة المرور
        </button>
    </form>
@endsection
