<?php

namespace App\Http\Controllers\OIDC;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\SsoSession;
use App\Services\OIDC\BackChannelLogoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Throwable;

class EndSessionController extends Controller
{
    public function __construct(private readonly BackChannelLogoutService $slo)
    {
    }

    public function __invoke(Request $request): RedirectResponse|View
    {
        $idTokenHint = $request->input('id_token_hint');
        $postLogoutRedirectUri = $request->input('post_logout_redirect_uri');
        $state = $request->input('state');

        $hintedSub = null;
        $hintedAud = null;

        if ($idTokenHint) {
            try {
                [$hintedSub, $hintedAud] = $this->parseIdTokenHint($idTokenHint);
            } catch (Throwable) {
                // Invalid hint — treat as missing.
            }
        }

        $user = Auth::user();

        if ($user) {
            $ssoSessionId = $request->session()->get('sso_session_id');
            $session = $ssoSessionId ? SsoSession::find($ssoSessionId) : null;

            if ($session) {
                $stats = $this->slo->logoutSession($session);

                $session->update([
                    'revoked' => true,
                    'revoked_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => $user->id,
                    'event_type' => AuditLog::EVENT_SLO_INITIATED,
                    'email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'client_id' => $hintedAud,
                    'metadata' => [
                        'sso_session_id' => $ssoSessionId,
                        'stats' => $stats,
                        'initiator' => 'rp_initiated',
                    ],
                ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_RP_LOGOUT_REQUESTED,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'client_id' => $hintedAud,
                'metadata' => [
                    'has_id_token_hint' => (bool) $idTokenHint,
                    'post_logout_redirect_uri' => $postLogoutRedirectUri,
                ],
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $validRedirect = $this->validatePostLogoutRedirectUri($postLogoutRedirectUri, $hintedAud);

        if ($validRedirect) {
            $url = $validRedirect;
            if ($state) {
                $url .= (str_contains($url, '?') ? '&' : '?').'state='.urlencode($state);
            }

            return redirect()->away($url);
        }

        return view('auth.logged-out', [
            'return_url' => route('login'),
        ]);
    }

    private function parseIdTokenHint(string $hint): array
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(config('oidc.keys.private')),
            InMemory::file(config('oidc.keys.public')),
        );

        $token = $config->parser()->parse($hint);

        return [
            $token->claims()->get('sub'),
            $token->claims()->get('aud')[0] ?? $token->claims()->get('aud'),
        ];
    }

    private function validatePostLogoutRedirectUri(?string $uri, ?string $clientId): ?string
    {
        if (! $uri || ! $clientId) {
            return null;
        }

        $client = Application::find($clientId);

        if (! $client) {
            return null;
        }

        $allowed = $client->redirect_uris ?? [];

        if (! is_array($allowed)) {
            return null;
        }

        foreach ($allowed as $allowedUri) {
            if ($this->urisMatch($uri, $allowedUri)) {
                return $uri;
            }

            $base = rtrim(parse_url($allowedUri, PHP_URL_SCHEME).'://'.parse_url($allowedUri, PHP_URL_HOST).(parse_url($allowedUri, PHP_URL_PORT) ? ':'.parse_url($allowedUri, PHP_URL_PORT) : ''), '/');
            if (str_starts_with($uri, $base)) {
                return $uri;
            }
        }

        return null;
    }

    private function urisMatch(string $a, string $b): bool
    {
        return rtrim($a, '/') === rtrim($b, '/');
    }
}
