<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>طلب صلاحية الوصول — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 p-8">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-200">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">{{ $client->name }}</h1>
                        <p class="text-sm text-slate-600">يطلب الوصول إلى حسابك</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-slate-700 mb-3">
                        مسجّل الدخول بـ: <span class="font-medium text-slate-900" dir="ltr">{{ $user->email }}</span>
                    </p>

                    <p class="text-sm font-medium text-slate-900 mb-2">الصلاحيات المطلوبة:</p>
                    <ul class="space-y-2">
                        @foreach ($scopes as $scope)
                            <li class="flex items-start gap-2 text-sm text-slate-700 bg-slate-50 rounded-lg px-3 py-2">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $scope->description }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="flex gap-3">
                    <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition">
                            السماح بالوصول
                        </button>
                    </form>

                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-2.5 px-4 rounded-lg transition">
                            رفض
                        </button>
                    </form>
                </div>

                <p class="text-xs text-slate-500 mt-6 text-center">
                    لست {{ $user->full_name }}؟
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-700 hover:underline">تسجيل الخروج</button>
                    </form>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
