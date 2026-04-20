@extends('layouts.admin')

@section('title', 'دليل التكامل')

@section('breadcrumbs')
    <a href="{{ route('admin.applications.index') }}" class="bc-link">الأنظمة</a>
    <span class="bc-sep">‹</span>
    <a href="{{ route('admin.applications.edit', $application) }}" class="bc-link">{{ $application->displayName() }}</a>
    <span class="bc-sep">‹</span>
    <span class="bc-current">دليل التكامل</span>
@endsection

@section('content')
    @php
        $copyable = [
            'Client ID' => $application->id,
            'Redirect URI' => $redirect_uri,
            'Discovery' => $endpoints['discovery'],
            'JWKS URL' => $endpoints['jwks'],
            'Authorization' => $endpoints['authorize'],
            'Token' => $endpoints['token'],
            'UserInfo' => $endpoints['userinfo'],
            'Scopes' => $scopes,
        ];
    @endphp

    <style>
        .doc-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; }
        .doc-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .doc-value { display: flex; align-items: center; gap: .5rem; }
        .doc-input { flex: 1; font-family: monospace; font-size: 12px; padding: .6rem .75rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #0f172a; overflow-x: auto; white-space: nowrap; }
        .copy-btn { padding: .6rem .85rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569; font-size: 11px; font-weight: 600; cursor: pointer; transition: all .15s; white-space: nowrap; }
        .copy-btn:hover { background: #e2e8f0; color: #0f172a; }
        .copy-btn.copied { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }

        .code-block {
            background: #0f172a; color: #e2e8f0;
            border-radius: 10px; padding: 1rem;
            font-family: ui-monospace, 'Cascadia Code', monospace;
            font-size: 12px; line-height: 1.55;
            overflow-x: auto; direction: ltr;
            position: relative;
        }
        .code-block .copy-code {
            position: absolute; top: .6rem; left: .6rem;
            background: rgba(255,255,255,.08); color: #cbd5e1;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 6px; padding: .3rem .6rem;
            font-size: 10px; cursor: pointer; font-family: inherit;
        }
        .code-block .copy-code:hover { background: rgba(255,255,255,.14); color: white; }
        .code-keyword { color: #f472b6; }
        .code-string { color: #86efac; }
        .code-fn { color: #93c5fd; }
        .code-comment { color: #64748b; font-style: italic; }

        .fw-tabs { display: flex; gap: .25rem; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: .3rem; }
        .fw-tab { padding: .6rem 1rem; border-radius: 8px; font-size: .85rem; font-weight: 500; color: #64748b; cursor: pointer; transition: all .15s; }
        .fw-tab:hover { color: #0f172a; background: #f8fafc; }
        .fw-tab.active { background: linear-gradient(135deg, var(--accent-color), var(--accent-end)); color: white; font-weight: 600; }

        .step-num { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; background: var(--accent-color); color: white; font-weight: 700; font-size: 12px; margin-left: .5rem; }
    </style>

    <div class="mb-6">
        <div class="flex items-center gap-4 p-5 rounded-2xl" style="background: linear-gradient(135deg, {{ $application->color }}, color-mix(in srgb, {{ $application->color }} 70%, black)); color: white;">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-xl bg-white/20 backdrop-blur border border-white/30">
                {{ $application->initial() }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold">{{ $application->displayName() }}</h1>
                <p class="text-sm text-white/75 truncate">{{ $application->description ?? 'دليل التكامل للمطوّرين' }}</p>
            </div>
            @if ($application->revoked)
                <span class="badge badge-danger">معطّل</span>
            @else
                <span class="badge badge-success">نشط</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="doc-card">
            <div class="flex items-start gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">ما عليك أنت</p>
                    <p class="text-xs text-slate-500">Admin</p>
                </div>
            </div>
            <ul class="text-xs text-slate-600 space-y-1 list-disc pr-4">
                <li>إعطاء المطوّر الـ Client ID + Secret (أسفل ↓)</li>
                <li>تسجيل redirect URIs الصحيحة</li>
                <li>إخباره بـ endpoints الـ SSO</li>
            </ul>
        </div>
        <div class="doc-card">
            <div class="flex items-start gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">ما على المطوّر</p>
                    <p class="text-xs text-slate-500">Developer</p>
                </div>
            </div>
            <ul class="text-xs text-slate-600 space-y-1 list-disc pr-4">
                <li>تثبيت OAuth2 client library</li>
                <li>إعداد 3 routes (login, callback, logout)</li>
                <li>استبدال نموذج login الحالي بـ SSO</li>
                <li>ربط بجدول users عبر email</li>
            </ul>
        </div>
        <div class="doc-card">
            <div class="flex items-start gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">ما لا يتغيّر</p>
                    <p class="text-xs text-slate-500">System DB</p>
                </div>
            </div>
            <ul class="text-xs text-slate-600 space-y-1 list-disc pr-4">
                <li>جدول users في نظامهم</li>
                <li>الصلاحيات والأدوار الداخلية</li>
                <li>البيانات الحالية (لن تُمسّ)</li>
            </ul>
        </div>
    </div>

    <div class="card-glass rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="step-num">1</span>
            <span>بيانات الاعتماد والـ Endpoints</span>
        </h2>
        <p class="text-xs text-slate-500 mb-5">اعطي هذه البيانات للمطوّر. كل حقل قابل للنسخ.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($copyable as $label => $value)
                <div>
                    <div class="doc-label">{{ $label }}</div>
                    <div class="doc-value">
                        <input readonly value="{{ $value }}" class="doc-input" dir="ltr">
                        <button type="button" class="copy-btn" data-copy="{{ $value }}">نسخ</button>
                    </div>
                </div>
            @endforeach

            <div class="md:col-span-2">
                <div class="doc-label">Client Secret</div>
                <div class="doc-value">
                    <input readonly value="••••••••••••••••••••••••••••••••" class="doc-input" dir="ltr">
                    <button type="button" id="rotate-btn" class="copy-btn" style="background: #fffbeb; color: #b45309; border-color: #fde68a;">توليد جديد</button>
                </div>
                <p class="mt-1.5 text-[11px] text-slate-500">الـ Secret يظهر مرة واحدة فقط عند الإنشاء. توليد جديد سيُبطل القديم فوراً.</p>
            </div>
        </div>
    </div>

    <div class="card-glass rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="step-num">2</span>
            <span>سير العمل (Authorization Code + PKCE)</span>
        </h2>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 font-mono text-xs text-slate-700 leading-relaxed overflow-x-auto" dir="ltr">
<pre style="margin:0; font-family:inherit">User → Client System → SSO Server

1. User clicks "Login with SSO" on Client System
2. Client System generates PKCE code_verifier & code_challenge
3. Client redirects user to:
   <span style="color:#2563eb">{{ $endpoints['authorize'] }}</span>
     ?client_id={{ $application->id }}
     &redirect_uri={{ urlencode($redirect_uri) }}
     &response_type=code
     &scope=openid+profile+email
     &state=RANDOM_STATE
     &code_challenge=PKCE_CHALLENGE
     &code_challenge_method=S256
4. User logs in (or already logged in) at SSO
5. User approves the app (first time only)
6. SSO redirects back to:
   <span style="color:#2563eb">{{ $redirect_uri }}</span>?code=AUTH_CODE&state=RANDOM_STATE
7. Client exchanges code for tokens (server-side POST):
   POST <span style="color:#2563eb">{{ $endpoints['token'] }}</span>
     grant_type=authorization_code
     client_id={{ $application->id }}
     client_secret=YOUR_SECRET
     code=AUTH_CODE
     redirect_uri={{ $redirect_uri }}
     code_verifier=PKCE_VERIFIER
8. Response: { access_token, id_token, refresh_token }
9. Client verifies id_token (JWT, RS256) using JWKS
10. Client extracts user info from id_token (email, name, sub)
11. Client creates local session → user is logged in ✓</pre>
        </div>
    </div>

    <div class="card-glass rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="step-num">3</span>
            <span>الكود الجاهز للنسخ (حسب التقنية)</span>
        </h2>

        <div class="fw-tabs mb-5" id="fw-tabs">
            <button type="button" class="fw-tab active" data-fw="laravel">Laravel</button>
            <button type="button" class="fw-tab" data-fw="ci3">CodeIgniter 3</button>
            <button type="button" class="fw-tab" data-fw="ci4">CodeIgniter 4</button>
            <button type="button" class="fw-tab" data-fw="nextjs">Next.js</button>
        </div>

        <div data-fw-content="laravel">
            <p class="text-xs text-slate-500 mb-2">1. ثبّت مكتبة Socialite مع provider مخصّص أو استخدم `league/oauth2-client`:</p>
            <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>composer require league/oauth2-client</pre></div>

            <p class="text-xs text-slate-500 mb-2">2. أضف المتغيرات في <code>.env</code>:</p>
            <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>SSO_CLIENT_ID={{ $application->id }}
SSO_CLIENT_SECRET=your-secret-here
SSO_REDIRECT_URI={{ $redirect_uri }}
SSO_AUTHORIZE={{ $endpoints['authorize'] }}
SSO_TOKEN={{ $endpoints['token'] }}
SSO_USERINFO={{ $endpoints['userinfo'] }}</pre></div>

            <p class="text-xs text-slate-500 mb-2">3. Controller (routes/web.php → AuthController):</p>
            <div class="code-block"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre><span class="code-comment">// routes/web.php</span>
Route::get(<span class="code-string">'/auth/sso'</span>, [AuthController::<span class="code-keyword">class</span>, <span class="code-string">'redirect'</span>]);
Route::get(<span class="code-string">'/auth/callback'</span>, [AuthController::<span class="code-keyword">class</span>, <span class="code-string">'callback'</span>]);
Route::post(<span class="code-string">'/logout'</span>, [AuthController::<span class="code-keyword">class</span>, <span class="code-string">'logout'</span>]);

<span class="code-comment">// app/Http/Controllers/AuthController.php</span>
<span class="code-keyword">use</span> League\OAuth2\Client\Provider\GenericProvider;

<span class="code-keyword">private function</span> <span class="code-fn">provider</span>() {
    <span class="code-keyword">return new</span> GenericProvider([
        <span class="code-string">'clientId'</span>     => env(<span class="code-string">'SSO_CLIENT_ID'</span>),
        <span class="code-string">'clientSecret'</span> => env(<span class="code-string">'SSO_CLIENT_SECRET'</span>),
        <span class="code-string">'redirectUri'</span>  => env(<span class="code-string">'SSO_REDIRECT_URI'</span>),
        <span class="code-string">'urlAuthorize'</span>            => env(<span class="code-string">'SSO_AUTHORIZE'</span>),
        <span class="code-string">'urlAccessToken'</span>          => env(<span class="code-string">'SSO_TOKEN'</span>),
        <span class="code-string">'urlResourceOwnerDetails'</span> => env(<span class="code-string">'SSO_USERINFO'</span>),
        <span class="code-string">'scopes'</span> => <span class="code-string">'openid profile email'</span>,
        <span class="code-string">'pkceMethod'</span> => <span class="code-string">'S256'</span>,
    ]);
}

<span class="code-keyword">public function</span> <span class="code-fn">redirect</span>(Request $request) {
    $provider = $this-><span class="code-fn">provider</span>();
    $authUrl = $provider-><span class="code-fn">getAuthorizationUrl</span>();
    $request->session()-><span class="code-fn">put</span>(<span class="code-string">'oauth2state'</span>, $provider-><span class="code-fn">getState</span>());
    $request->session()-><span class="code-fn">put</span>(<span class="code-string">'oauth2pkceCode'</span>, $provider-><span class="code-fn">getPkceCode</span>());
    <span class="code-keyword">return</span> <span class="code-fn">redirect</span>($authUrl);
}

<span class="code-keyword">public function</span> <span class="code-fn">callback</span>(Request $request) {
    <span class="code-keyword">if</span> ($request-><span class="code-fn">get</span>(<span class="code-string">'state'</span>) !== $request->session()-><span class="code-fn">pull</span>(<span class="code-string">'oauth2state'</span>)) {
        <span class="code-keyword">abort</span>(403, <span class="code-string">'Invalid state'</span>);
    }
    $provider = $this-><span class="code-fn">provider</span>();
    $provider-><span class="code-fn">setPkceCode</span>($request->session()-><span class="code-fn">pull</span>(<span class="code-string">'oauth2pkceCode'</span>));
    $token = $provider-><span class="code-fn">getAccessToken</span>(<span class="code-string">'authorization_code'</span>, [
        <span class="code-string">'code'</span> => $request-><span class="code-fn">get</span>(<span class="code-string">'code'</span>),
    ]);
    $ssoUser = $provider-><span class="code-fn">getResourceOwner</span>($token)-><span class="code-fn">toArray</span>();

    <span class="code-comment">// Find or create local user by email</span>
    $user = User::<span class="code-fn">firstOrCreate</span>(
        [<span class="code-string">'email'</span> => $ssoUser[<span class="code-string">'email'</span>]],
        [<span class="code-string">'name'</span> => $ssoUser[<span class="code-string">'name'</span>], <span class="code-string">'password'</span> => <span class="code-fn">bcrypt</span>(<span class="code-fn">str_random</span>(32))]
    );
    Auth::<span class="code-fn">login</span>($user);
    <span class="code-keyword">return</span> <span class="code-fn">redirect</span>(<span class="code-string">'/dashboard'</span>);
}</pre></div>
        </div>

        <div data-fw-content="ci3" class="hidden">
            <p class="text-xs text-slate-500 mb-2">1. تثبيت oauth2-client عبر composer في جذر CI3:</p>
            <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>composer require league/oauth2-client</pre></div>

            <p class="text-xs text-slate-500 mb-2">2. Controller (application/controllers/Sso.php):</p>
            <div class="code-block"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre><span class="code-keyword">&lt;?php</span>
defined(<span class="code-string">'BASEPATH'</span>) OR exit(<span class="code-string">'No direct script access allowed'</span>);
<span class="code-keyword">require_once</span> FCPATH.<span class="code-string">'vendor/autoload.php'</span>;

<span class="code-keyword">use</span> League\OAuth2\Client\Provider\GenericProvider;

<span class="code-keyword">class</span> Sso <span class="code-keyword">extends</span> CI_Controller {
    <span class="code-keyword">private</span> $provider;

    <span class="code-keyword">public function</span> __construct() {
        <span class="code-keyword">parent</span>::__construct();
        $this-><span class="code-fn">load</span>->library(<span class="code-string">'session'</span>);
        $this->provider = <span class="code-keyword">new</span> GenericProvider([
            <span class="code-string">'clientId'</span>     => <span class="code-string">'{{ $application->id }}'</span>,
            <span class="code-string">'clientSecret'</span> => <span class="code-string">'YOUR_SECRET'</span>,
            <span class="code-string">'redirectUri'</span>  => <span class="code-string">'{{ $redirect_uri }}'</span>,
            <span class="code-string">'urlAuthorize'</span>            => <span class="code-string">'{{ $endpoints['authorize'] }}'</span>,
            <span class="code-string">'urlAccessToken'</span>          => <span class="code-string">'{{ $endpoints['token'] }}'</span>,
            <span class="code-string">'urlResourceOwnerDetails'</span> => <span class="code-string">'{{ $endpoints['userinfo'] }}'</span>,
            <span class="code-string">'scopes'</span> => <span class="code-string">'openid profile email'</span>,
            <span class="code-string">'pkceMethod'</span> => <span class="code-string">'S256'</span>,
        ]);
    }

    <span class="code-keyword">public function</span> <span class="code-fn">login</span>() {
        $authUrl = $this->provider-><span class="code-fn">getAuthorizationUrl</span>();
        $this-><span class="code-fn">session</span>->set_userdata(<span class="code-string">'oauth2state'</span>, $this->provider-><span class="code-fn">getState</span>());
        $this-><span class="code-fn">session</span>->set_userdata(<span class="code-string">'oauth2pkceCode'</span>, $this->provider-><span class="code-fn">getPkceCode</span>());
        <span class="code-fn">redirect</span>($authUrl);
    }

    <span class="code-keyword">public function</span> <span class="code-fn">callback</span>() {
        <span class="code-keyword">if</span> ($this->input-><span class="code-fn">get</span>(<span class="code-string">'state'</span>) !== $this-><span class="code-fn">session</span>->userdata(<span class="code-string">'oauth2state'</span>)) {
            <span class="code-fn">show_error</span>(<span class="code-string">'Invalid state'</span>, 403);
        }
        $this->provider-><span class="code-fn">setPkceCode</span>($this-><span class="code-fn">session</span>->userdata(<span class="code-string">'oauth2pkceCode'</span>));
        $token = $this->provider-><span class="code-fn">getAccessToken</span>(<span class="code-string">'authorization_code'</span>, [
            <span class="code-string">'code'</span> => $this->input-><span class="code-fn">get</span>(<span class="code-string">'code'</span>),
        ]);
        $ssoUser = $this->provider-><span class="code-fn">getResourceOwner</span>($token)-><span class="code-fn">toArray</span>();

        <span class="code-comment">// Find user by email in your existing users table</span>
        $this-><span class="code-fn">load</span>->model(<span class="code-string">'User_model'</span>);
        $user = $this->User_model-><span class="code-fn">find_or_create_by_email</span>($ssoUser[<span class="code-string">'email'</span>], $ssoUser);
        $this-><span class="code-fn">session</span>->set_userdata(<span class="code-string">'user_id'</span>, $user->id);
        <span class="code-fn">redirect</span>(<span class="code-string">'/dashboard'</span>);
    }
}</pre></div>
        </div>

        <div data-fw-content="ci4" class="hidden">
            <p class="text-xs text-slate-500 mb-2">Controller (app/Controllers/Sso.php):</p>
            <div class="code-block"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">namespace</span> App\Controllers;
<span class="code-keyword">use</span> League\OAuth2\Client\Provider\GenericProvider;

<span class="code-keyword">class</span> Sso <span class="code-keyword">extends</span> BaseController
{
    <span class="code-keyword">private function</span> <span class="code-fn">provider</span>(): GenericProvider {
        <span class="code-keyword">return new</span> GenericProvider([
            <span class="code-string">'clientId'</span>     => env(<span class="code-string">'SSO_CLIENT_ID'</span>, <span class="code-string">'{{ $application->id }}'</span>),
            <span class="code-string">'clientSecret'</span> => env(<span class="code-string">'SSO_CLIENT_SECRET'</span>),
            <span class="code-string">'redirectUri'</span>  => <span class="code-string">'{{ $redirect_uri }}'</span>,
            <span class="code-string">'urlAuthorize'</span>            => <span class="code-string">'{{ $endpoints['authorize'] }}'</span>,
            <span class="code-string">'urlAccessToken'</span>          => <span class="code-string">'{{ $endpoints['token'] }}'</span>,
            <span class="code-string">'urlResourceOwnerDetails'</span> => <span class="code-string">'{{ $endpoints['userinfo'] }}'</span>,
            <span class="code-string">'scopes'</span> => <span class="code-string">'openid profile email'</span>,
            <span class="code-string">'pkceMethod'</span> => <span class="code-string">'S256'</span>,
        ]);
    }

    <span class="code-keyword">public function</span> <span class="code-fn">login</span>() {
        $p = $this-><span class="code-fn">provider</span>();
        $authUrl = $p-><span class="code-fn">getAuthorizationUrl</span>();
        session()-><span class="code-fn">set</span>([
            <span class="code-string">'oauth2state'</span> => $p-><span class="code-fn">getState</span>(),
            <span class="code-string">'oauth2pkce'</span>  => $p-><span class="code-fn">getPkceCode</span>(),
        ]);
        <span class="code-keyword">return</span> <span class="code-fn">redirect</span>()-><span class="code-fn">to</span>($authUrl);
    }

    <span class="code-keyword">public function</span> <span class="code-fn">callback</span>() {
        <span class="code-keyword">if</span> ($this-><span class="code-fn">request</span>-><span class="code-fn">getGet</span>(<span class="code-string">'state'</span>) !== session(<span class="code-string">'oauth2state'</span>)) {
            <span class="code-keyword">throw</span> \CodeIgniter\Exceptions\PageNotFoundException::<span class="code-fn">forPageNotFound</span>();
        }
        $p = $this-><span class="code-fn">provider</span>();
        $p-><span class="code-fn">setPkceCode</span>(session(<span class="code-string">'oauth2pkce'</span>));
        $token = $p-><span class="code-fn">getAccessToken</span>(<span class="code-string">'authorization_code'</span>, [
            <span class="code-string">'code'</span> => $this-><span class="code-fn">request</span>-><span class="code-fn">getGet</span>(<span class="code-string">'code'</span>),
        ]);
        $ssoUser = $p-><span class="code-fn">getResourceOwner</span>($token)-><span class="code-fn">toArray</span>();

        $userModel = <span class="code-keyword">new</span> \App\Models\UserModel();
        $user = $userModel-><span class="code-fn">findOrCreateByEmail</span>($ssoUser[<span class="code-string">'email'</span>], $ssoUser);
        session()-><span class="code-fn">set</span>(<span class="code-string">'user_id'</span>, $user->id);
        <span class="code-keyword">return</span> <span class="code-fn">redirect</span>()-><span class="code-fn">to</span>(<span class="code-string">'/dashboard'</span>);
    }
}</pre></div>
        </div>

        <div data-fw-content="nextjs" class="hidden">
            <p class="text-xs text-slate-500 mb-2">1. تثبيت next-auth:</p>
            <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>npm install next-auth</pre></div>

            <p class="text-xs text-slate-500 mb-2">2. <code>pages/api/auth/[...nextauth].ts</code>:</p>
            <div class="code-block"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre><span class="code-keyword">import</span> NextAuth <span class="code-keyword">from</span> <span class="code-string">"next-auth"</span>;

<span class="code-keyword">export default</span> <span class="code-fn">NextAuth</span>({
  providers: [
    {
      id: <span class="code-string">"gedco-sso"</span>,
      name: <span class="code-string">"GEDCO SSO"</span>,
      type: <span class="code-string">"oauth"</span>,
      wellKnown: <span class="code-string">"{{ $endpoints['discovery'] }}"</span>,
      authorization: { params: { scope: <span class="code-string">"openid profile email"</span> } },
      clientId: process.env.<span class="code-fn">SSO_CLIENT_ID</span>,
      clientSecret: process.env.<span class="code-fn">SSO_CLIENT_SECRET</span>,
      idToken: <span class="code-keyword">true</span>,
      checks: [<span class="code-string">"pkce"</span>, <span class="code-string">"state"</span>],
      profile(profile) {
        <span class="code-keyword">return</span> {
          id: profile.<span class="code-fn">sub</span>,
          name: profile.<span class="code-fn">name</span>,
          email: profile.<span class="code-fn">email</span>,
        };
      },
    },
  ],
  callbacks: {
    <span class="code-keyword">async</span> <span class="code-fn">signIn</span>({ user }) {
      <span class="code-comment">// Sync with your local DB: find by email, create if new</span>
      <span class="code-keyword">await</span> <span class="code-fn">fetch</span>(<span class="code-string">"http://your-api/sync-user"</span>, {
        method: <span class="code-string">"POST"</span>,
        body: <span class="code-fn">JSON.stringify</span>(user),
      });
      <span class="code-keyword">return true</span>;
    },
  },
});</pre></div>

            <p class="text-xs text-slate-500 mt-3 mb-2">3. <code>.env.local</code>:</p>
            <div class="code-block"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>NEXTAUTH_URL=http://localhost:8005
NEXTAUTH_SECRET=generate-random-secret-here
SSO_CLIENT_ID={{ $application->id }}
SSO_CLIENT_SECRET=your-secret-here</pre></div>
        </div>
    </div>

    <div class="card-glass rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="step-num">4</span>
            <span>Single Logout (SLO) — Back-channel</span>
        </h2>

        <p class="text-sm text-slate-600 mb-4">
            لما المستخدم يعمل logout من الـ IdP (أو من أي نظام عميل يستخدم <code>end_session_endpoint</code>)،
            بنبعتلكم POST request فيه <code>logout_token</code> (JWT موقّع بـ RS256).
            نظامكم لازم يتحقق من التوقيع ويحذف الـ session المحلية للمستخدم.
        </p>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div class="doc-card">
                <div class="doc-label">End Session Endpoint (RP-initiated)</div>
                <div class="doc-value">
                    <div class="doc-input" dir="ltr">{{ $issuer }}/oauth/logout</div>
                    <button class="copy-btn" data-copy="{{ $issuer }}/oauth/logout">نسخ</button>
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    وجّه المستخدم لهنا مع <code>id_token_hint</code> و <code>post_logout_redirect_uri</code>
                </p>
            </div>
            <div class="doc-card">
                <div class="doc-label">Back-Channel Logout URI (عندكم)</div>
                <div class="doc-value">
                    <div class="doc-input" dir="ltr">{{ $application->back_channel_logout_uri ?: '— لم يُكوَّن بعد —' }}</div>
                    @if ($application->back_channel_logout_uri)
                        <button class="copy-btn" data-copy="{{ $application->back_channel_logout_uri }}">نسخ</button>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    يُعدَّل من صفحة <a href="{{ route('admin.applications.edit', $application) }}" class="text-blue-600 underline">تعديل التطبيق</a>
                </p>
            </div>
        </div>

        <p class="text-xs font-semibold text-slate-700 mb-2">logout_token مثال (JWT payload):</p>
        <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre>{
  <span class="code-string">"iss"</span>: <span class="code-string">"{{ $issuer }}"</span>,
  <span class="code-string">"sub"</span>: <span class="code-string">"user-uuid"</span>,
  <span class="code-string">"aud"</span>: <span class="code-string">"{{ $application->id }}"</span>,
  <span class="code-string">"iat"</span>: 1234567890,
  <span class="code-string">"exp"</span>: 1234568010,
  <span class="code-string">"jti"</span>: <span class="code-string">"unique-token-id"</span>,
  <span class="code-string">"sid"</span>: <span class="code-string">"session-id-from-id_token"</span>,
  <span class="code-string">"events"</span>: {
    <span class="code-string">"http://schemas.openid.net/event/backchannel-logout"</span>: {}
  }
}</pre></div>

        <p class="text-xs font-semibold text-slate-700 mb-2">Laravel — نقطة نهاية الاستقبال:</p>
        <div class="code-block mb-4"><button class="copy-code" onclick="copyCode(this)">نسخ</button><pre><span class="code-comment">// routes/web.php — بدون CSRF</span>
Route::post(<span class="code-string">'/sso/back-channel-logout'</span>, [SsoController::<span class="code-keyword">class</span>, <span class="code-string">'backChannelLogout'</span>])
    -&gt;withoutMiddleware([VerifyCsrfToken::<span class="code-keyword">class</span>]);

<span class="code-comment">// SsoController.php</span>
<span class="code-keyword">use</span> Firebase\JWT\JWT;
<span class="code-keyword">use</span> Firebase\JWT\JWK;

<span class="code-keyword">public function</span> backChannelLogout(Request $request) {
    $logoutToken = $request-&gt;input(<span class="code-string">'logout_token'</span>);
    <span class="code-keyword">if</span> (!$logoutToken) <span class="code-keyword">return</span> response()-&gt;json([<span class="code-string">'error'</span> =&gt; <span class="code-string">'missing_token'</span>], 400);

    <span class="code-comment">// 1. حمّل الـ JWKS (cache ل 12 ساعة)</span>
    $jwks = Http::get(<span class="code-string">'{{ $endpoints['jwks'] }}'</span>)-&gt;json();

    <span class="code-keyword">try</span> {
        $claims = JWT::decode($logoutToken, JWK::parseKeySet($jwks));
    } <span class="code-keyword">catch</span> (Throwable $e) {
        <span class="code-keyword">return</span> response()-&gt;json([<span class="code-string">'error'</span> =&gt; <span class="code-string">'invalid_token'</span>], 400);
    }

    <span class="code-comment">// 2. تحقق iss + aud + events</span>
    <span class="code-keyword">if</span> ($claims-&gt;iss !== <span class="code-string">'{{ $issuer }}'</span>) <span class="code-keyword">return</span> response(<span class="code-string">''</span>, 400);
    <span class="code-keyword">if</span> ($claims-&gt;aud !== <span class="code-string">'{{ $application->id }}'</span>) <span class="code-keyword">return</span> response(<span class="code-string">''</span>, 400);
    <span class="code-keyword">if</span> (!isset($claims-&gt;events-&gt;{<span class="code-string">'http://schemas.openid.net/event/backchannel-logout'</span>})) <span class="code-keyword">return</span> response(<span class="code-string">''</span>, 400);

    <span class="code-comment">// 3. امسح session المستخدم (sub = user uuid أو sid = session id)</span>
    DB::table(<span class="code-string">'sessions'</span>)-&gt;where(<span class="code-string">'user_id'</span>, $claims-&gt;sub)-&gt;delete();

    <span class="code-keyword">return</span> response()-&gt;json([<span class="code-string">'ok'</span> =&gt; <span class="code-keyword">true</span>]);
}</pre></div>

        <div class="p-3 rounded-lg text-xs" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e">
            <strong>⚠️ ملاحظة:</strong>
            الـ endpoint لازم يكون <code>POST</code> و بدون CSRF protection (الطلب جاي من server-to-server).
            تجاوب بـ <code>200 OK</code> لما تنجح، أو <code>400</code> لو الـ token غير صالح.
            Timeout عندنا 5 ثواني — خلي الـ endpoint سريع.
        </div>
    </div>

    <div class="card-glass rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="step-num">5</span>
            <span>قائمة الاختبار (Checklist)</span>
        </h2>
        <div class="space-y-2">
            @foreach ([
                'الـ Client ID و Secret مخزّنان في .env، ليسا في الكود',
                'الـ Redirect URI مطابق تماماً (بما فيه https/http + slash)',
                'PKCE مُفعّل (code_challenge_method=S256)',
                'state parameter يُتحقّق منه في الـ callback',
                'id_token يُتحقّق من توقيعه عبر JWKS',
                'ربط المستخدم بجدول users الحالي عبر email',
                'زر logout ينظّف الـ session المحلية',
                'اختبار مع مستخدم موجود + مستخدم جديد',
                'Back-channel logout URI مُكوَّن ومُختبَر (يستقبل JWT ويحذف session)',
                'التحقق من توقيع logout_token عبر JWKS',
            ] as $item)
                <label class="flex items-start gap-2.5 cursor-pointer p-3 rounded-lg hover:bg-slate-50 transition">
                    <input type="checkbox" class="mt-0.5 w-4 h-4 rounded" style="accent-color: var(--accent-color)">
                    <span class="text-sm text-slate-700">{{ $item }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy]').forEach(btn => {
                btn.addEventListener('click', () => {
                    navigator.clipboard.writeText(btn.dataset.copy);
                    btn.classList.add('copied');
                    const old = btn.textContent;
                    btn.textContent = '✓ تم النسخ';
                    setTimeout(() => { btn.textContent = old; btn.classList.remove('copied'); }, 1500);
                });
            });

            $('#fw-tabs .fw-tab').on('click', function () {
                const fw = $(this).data('fw');
                $('#fw-tabs .fw-tab').removeClass('active');
                $(this).addClass('active');
                $('[data-fw-content]').addClass('hidden');
                $(`[data-fw-content="${fw}"]`).removeClass('hidden');
            });

            $('#rotate-btn').on('click', function () {
                Swal.fire({
                    title: 'توليد Client Secret جديد؟',
                    html: '<strong style="color:#fcd34d">سيتوقّف المفتاح القديم فوراً.</strong> تطبيقات النظام ستحتاج لتحديث الـ secret.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، ولّد',
                    cancelButtonText: 'إلغاء',
                }).then((r) => {
                    if (!r.isConfirmed) return;
                    $.post(`/admin/applications/{{ $application->id }}/rotate-secret`)
                        .done(d => {
                            Swal.fire({
                                title: d.message,
                                html: `<p style="color:rgba(255,255,255,.7);margin:1rem 0">الـ Secret الجديد:</p>
                                       <input readonly value="${d.client_secret}" dir="ltr"
                                           style="width:100%;padding:.75rem;background:rgba(245,158,11,.08);border:1.5px solid rgba(251,191,36,.3);border-radius:8px;color:#fcd34d;font-family:monospace;font-size:12px;text-align:center">
                                       <button onclick="navigator.clipboard.writeText('${d.client_secret}'); toastr.success('تم النسخ')"
                                           style="margin-top:.75rem;padding:.5rem 1rem;background:linear-gradient(135deg,#F97316,#FBBF24);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer">نسخ</button>`,
                                icon: 'success',
                            });
                        })
                        .fail(() => toastr.error('فشل التوليد'));
                });
            });
        });

        function copyCode(btn) {
            const pre = btn.parentElement.querySelector('pre');
            navigator.clipboard.writeText(pre.innerText);
            const old = btn.textContent;
            btn.textContent = '✓ نُسخ';
            setTimeout(() => { btn.textContent = old; }, 1500);
        }
    </script>
@endsection
