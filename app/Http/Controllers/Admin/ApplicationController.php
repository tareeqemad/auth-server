<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Application::query()->orderBy('created_at', 'desc');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name_ar', 'like', "%{$search}%")
                    ->orWhere('display_name_en', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            match ($status) {
                'active' => $query->where('revoked', false),
                'revoked' => $query->where('revoked', true),
                default => null,
            };
        }

        $applications = $query->paginate(15)->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.applications.form', [
            'application' => new Application([
                'color' => '#0a2540',
                'is_first_party' => true,
                'grant_types' => ['authorization_code', 'refresh_token'],
                'redirect_uris' => [],
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validateData($request);

        $plainSecret = Str::random(40);

        $app = Application::create([
            'name' => $data['display_name_ar'],
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'display_name_en' => $data['display_name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'],
            'launch_url' => $data['launch_url'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'is_first_party' => $data['is_first_party'] ?? true,
            'redirect_uris' => $data['redirect_uris'],
            'grant_types' => $data['grant_types'],
            'secret' => Hash::make($plainSecret),
            'provider' => 'users',
            'revoked' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء النظام بنجاح.',
                'application' => [
                    'id' => $app->id,
                    'slug' => $app->slug,
                    'display_name_ar' => $app->display_name_ar,
                ],
                'credentials' => [
                    'client_id' => $app->id,
                    'client_secret' => $plainSecret,
                ],
                'redirect' => route('admin.applications.index'),
            ]);
        }

        return redirect()
            ->route('admin.applications.index')
            ->with('status', 'تم إنشاء النظام بنجاح.');
    }

    public function edit(Application $application): View
    {
        return view('admin.applications.form', [
            'application' => $application,
            'mode' => 'edit',
        ]);
    }

    public function integration(Application $application): View
    {
        $issuer = rtrim(config('oidc.issuer', config('app.url')), '/');

        return view('admin.applications.integration', [
            'application' => $application,
            'issuer' => $issuer,
            'endpoints' => [
                'discovery' => $issuer.'/.well-known/openid-configuration',
                'jwks' => $issuer.'/.well-known/jwks.json',
                'authorize' => $issuer.'/oauth/authorize',
                'token' => $issuer.'/oauth/token',
                'userinfo' => $issuer.'/oauth/userinfo',
            ],
            'redirect_uri' => $application->firstRedirectUri() ?? 'https://your-app.example/auth/callback',
            'scopes' => 'openid profile email phone',
        ]);
    }

    public function update(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $data = $this->validateData($request, $application->id);

        $application->update([
            'name' => $data['display_name_ar'],
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'display_name_en' => $data['display_name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'],
            'launch_url' => $data['launch_url'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'is_first_party' => $data['is_first_party'] ?? false,
            'redirect_uris' => $data['redirect_uris'],
            'grant_types' => $data['grant_types'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التغييرات.',
                'redirect' => route('admin.applications.index'),
            ]);
        }

        return redirect()
            ->route('admin.applications.index')
            ->with('status', 'تم حفظ التغييرات.');
    }

    public function destroy(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $application->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف النظام.',
            ]);
        }

        return back()->with('status', 'تم حذف النظام.');
    }

    public function rotateSecret(Request $request, Application $application): JsonResponse
    {
        $plainSecret = Str::random(40);
        $application->secret = Hash::make($plainSecret);
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'تم توليد مفتاح سري جديد. احفظه الآن — لن يظهر مرة أخرى.',
            'client_id' => $application->id,
            'client_secret' => $plainSecret,
        ]);
    }

    public function toggleRevoke(Request $request, Application $application): JsonResponse
    {
        $application->revoked = ! $application->revoked;
        $application->save();

        return response()->json([
            'success' => true,
            'message' => $application->revoked
                ? 'تم إيقاف النظام. لن يتمكّن مستخدموه من الدخول.'
                : 'تم تفعيل النظام.',
            'revoked' => $application->revoked,
        ]);
    }

    private function validateData(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'slug' => [
                'required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('oauth_clients', 'slug')->ignore($ignoreId),
            ],
            'display_name_ar' => ['required', 'string', 'max:255'],
            'display_name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'launch_url' => ['nullable', 'url', 'max:500'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'is_first_party' => ['nullable', 'boolean'],
            'redirect_uris' => ['required', 'array', 'min:1'],
            'redirect_uris.*' => ['required', 'url', 'max:500'],
            'grant_types' => ['required', 'array', 'min:1'],
            'grant_types.*' => ['required', Rule::in(['authorization_code', 'refresh_token', 'client_credentials'])],
        ], [], [
            'slug' => 'المعرّف',
            'display_name_ar' => 'الاسم بالعربي',
            'display_name_en' => 'الاسم بالإنجليزي',
            'description' => 'الوصف',
            'color' => 'اللون',
            'launch_url' => 'رابط الفتح',
            'logo_url' => 'رابط الشعار',
            'redirect_uris' => 'روابط التحويل',
            'redirect_uris.*' => 'رابط التحويل',
            'grant_types' => 'أنواع الدخول',
        ]);
    }
}
