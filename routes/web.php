<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController as AdminUsersController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SmsPasswordResetController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\TwoFactorSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OIDC\DiscoveryController;
use App\Http\Controllers\OIDC\JwksController;
use App\Http\Controllers\OIDC\UserInfoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');

    Route::get('/forgot-password/sms', [SmsPasswordResetController::class, 'showPhoneForm'])->name('password.sms.phone');
    Route::post('/forgot-password/sms/send', [SmsPasswordResetController::class, 'sendCode'])->name('password.sms.send');
    Route::get('/forgot-password/sms/verify', [SmsPasswordResetController::class, 'showVerifyForm'])->name('password.sms.verify.show');
    Route::post('/forgot-password/sms/verify', [SmsPasswordResetController::class, 'verifyCode'])->name('password.sms.verify');
    Route::get('/forgot-password/sms/reset', [SmsPasswordResetController::class, 'showResetForm'])->name('password.sms.reset.show');
    Route::post('/forgot-password/sms/reset', [SmsPasswordResetController::class, 'resetPassword'])->name('password.sms.reset');

    Route::get('/login/2fa', [TwoFactorController::class, 'show'])->name('login.2fa.show');
    Route::post('/login/2fa', [TwoFactorController::class, 'verify'])->name('login.2fa.verify');
    Route::post('/login/2fa/resend', [TwoFactorController::class, 'resend'])->name('login.2fa.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'showPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/sessions', [ProfileController::class, 'sessions'])->name('sessions');
        Route::post('/sessions/{session}/revoke', [ProfileController::class, 'revokeSession'])->name('sessions.revoke');
        Route::post('/sessions/revoke-all', [ProfileController::class, 'revokeAllSessions'])->name('sessions.revoke_all');
        Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');

        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::post('/2fa/enable/send', [TwoFactorSettingsController::class, 'enableSend'])->name('2fa.enable.send');
        Route::post('/2fa/enable/verify', [TwoFactorSettingsController::class, 'enableVerify'])->name('2fa.enable.verify');
        Route::post('/2fa/disable', [TwoFactorSettingsController::class, 'disable'])->name('2fa.disable');
    });

    Route::get('/dashboard', function () {
        $user = Auth::user();

        $applications = \App\Models\Application::query()
            ->whereNotNull('slug')
            ->where('revoked', false)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('slug');

        $linkedSystems = $user->systemLinks()
            ->orderByDesc('last_accessed_at')
            ->get()
            ->map(function ($link) use ($applications) {
                $app = $applications->get($link->system_name);
                if (! $app) {
                    return null;
                }

                $name = $app->display_name_ar ?: ($app->display_name_en ?: $app->slug);

                return [
                    'key' => $link->system_name,
                    'display_name' => $name,
                    'description' => $app->description ?: '',
                    'color' => $app->color ?: '#475569',
                    'launch_url' => $app->launch_url,
                    'external_user_id' => $link->external_user_id,
                    'linked_at' => $link->linked_at,
                    'last_accessed_at' => $link->last_accessed_at,
                    'initial' => mb_substr($name, 0, 1),
                ];
            })
            ->filter()
            ->values();

        return view('dashboard', [
            'user' => $user,
            'linkedSystems' => $linkedSystems,
        ]);
    })->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::post('applications/{application}/rotate-secret', [AdminApplicationController::class, 'rotateSecret'])->name('applications.rotate_secret');
    Route::post('applications/{application}/toggle-revoke', [AdminApplicationController::class, 'toggleRevoke'])->name('applications.toggle_revoke');
    Route::get('applications/{application}/integration', [AdminApplicationController::class, 'integration'])->name('applications.integration');
    Route::resource('applications', AdminApplicationController::class);

    Route::post('users/{user}/toggle-active', [AdminUsersController::class, 'toggleActive'])->name('users.toggle_active');
    Route::post('users/{user}/reset-password', [AdminUsersController::class, 'resetPassword'])->name('users.reset_password');
    Route::resource('users', AdminUsersController::class)->except(['show']);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

    Route::get('sessions', [AdminSessionController::class, 'index'])->name('sessions.index');
    Route::post('sessions/{session}/revoke', [AdminSessionController::class, 'revoke'])->name('sessions.revoke');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-sms', [SettingController::class, 'testSms'])->name('settings.test_sms');
    Route::get('settings/sms-balance', [SettingController::class, 'smsBalance'])->name('settings.sms_balance');

    Route::get('admins', [AdminUserController::class, 'index'])->name('admins.index');
    Route::post('admins/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('admins.assign_role');
    Route::post('admins/{user}/remove-role', [AdminUserController::class, 'removeRole'])->name('admins.remove_role');
});

Route::get('/.well-known/openid-configuration', DiscoveryController::class);
Route::get('/.well-known/jwks.json', JwksController::class);

Route::middleware('auth:api')->match(['get', 'post'], '/oauth/userinfo', UserInfoController::class);
