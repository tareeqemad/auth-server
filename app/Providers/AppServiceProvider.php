<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Passport::useClientModel(Application::class);

        Passport::tokensCan(config('oidc.scopes'));

        Passport::setDefaultScope(['openid']);

        Passport::enablePasswordGrant();

        Passport::authorizationView('oauth.authorize');

        $this->shareBrandSettings();
    }

    private function shareBrandSettings(): void
    {
        View::composer('*', function ($view) {
            if ($view->offsetExists('brand')) {
                return;
            }

            $view->with('brand', $this->buildBrandData());
        });
    }

    private function buildBrandData(): array
    {
        $defaults = [
            'company_ar' => 'شركة توزيع كهرباء غزة',
            'company_en' => 'GEDCO',
            'system_ar' => 'النظام الموحّد',
            'system_en' => 'Unified System',
            'tagline' => '',
            'logo' => null,
            'primary_color' => '#0f4c81',
            'accent_color' => '#F97316',
            'support_email' => '',
            'support_phone' => '',
            'applications' => [],
        ];

        try {
            if (! Schema::hasTable('settings')) {
                return $defaults;
            }

            $applications = Schema::hasTable('oauth_clients') && Schema::hasColumn('oauth_clients', 'color')
                ? Application::where('revoked', false)
                    ->whereNull('deleted_at')
                    ->whereNotNull('slug')
                    ->orderBy('slug')
                    ->get(['id', 'color', 'display_name_ar', 'slug'])
                    ->map(fn ($a) => [
                        'color' => $a->color ?: '#475569',
                        'name' => $a->display_name_ar ?: $a->slug,
                    ])
                    ->values()
                    ->toArray()
                : [];

            return [
                'company_ar' => Setting::get('company_name_ar', $defaults['company_ar']),
                'company_en' => Setting::get('company_name_en', $defaults['company_en']),
                'system_ar' => Setting::get('system_name_ar', $defaults['system_ar']),
                'system_en' => Setting::get('system_name_en', $defaults['system_en']),
                'tagline' => Setting::get('system_tagline', $defaults['tagline']),
                'logo' => Setting::get('logo_url'),
                'primary_color' => Setting::get('primary_color', $defaults['primary_color']),
                'accent_color' => Setting::get('accent_color', $defaults['accent_color']),
                'support_email' => Setting::get('support_email', $defaults['support_email']),
                'support_phone' => Setting::get('support_phone', $defaults['support_phone']),
                'applications' => $applications,
            ];
        } catch (\Throwable) {
            return $defaults;
        }
    }
}
