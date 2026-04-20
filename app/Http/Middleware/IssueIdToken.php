<?php

namespace App\Http\Middleware;

use App\Models\SsoSession;
use App\Models\SsoSessionClient;
use App\Models\User;
use App\Services\OIDC\IdTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IssueIdToken
{
    public function __construct(private readonly IdTokenService $idTokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('oauth/token')) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);

        if (! is_array($data) || ! isset($data['access_token'])) {
            return $response;
        }

        try {
            $parsed = $this->parseAccessToken($data['access_token']);
        } catch (Throwable) {
            return $response;
        }

        $scopes = $parsed['scopes'];

        if (! in_array('openid', $scopes, true)) {
            return $response;
        }

        if (empty($parsed['sub']) || empty($parsed['aud'])) {
            return $response;
        }

        $user = User::find($parsed['sub']);

        if (! $user) {
            return $response;
        }

        $sid = $this->ensureSessionClient($user->id, (string) $parsed['aud']);

        try {
            $idToken = $this->idTokens->issue(
                $user,
                (string) $parsed['aud'],
                $scopes,
                $request->input('nonce'),
                $sid,
            );
        } catch (Throwable) {
            return $response;
        }

        $data['id_token'] = $idToken;

        $response->setContent(json_encode($data));

        return $response;
    }

    private function parseAccessToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid JWT access token.');
        }

        $payload = json_decode(
            base64_decode(strtr($parts[1], '-_', '+/'), true) ?: '',
            true,
        );

        return [
            'sub' => $payload['sub'] ?? null,
            'aud' => $payload['aud'] ?? null,
            'scopes' => $payload['scopes'] ?? [],
        ];
    }

    private function ensureSessionClient(string $userId, string $clientId): ?string
    {
        try {
            $ssoSessionId = session('sso_session_id');

            if (! $ssoSessionId) {
                $ssoSessionId = SsoSession::where('user_id', $userId)
                    ->where('revoked', false)
                    ->where('expires_at', '>', now())
                    ->latest('last_activity_at')
                    ->value('id');
            }

            if (! $ssoSessionId) {
                return null;
            }

            $link = SsoSessionClient::where('sso_session_id', $ssoSessionId)
                ->where('client_id', $clientId)
                ->first();

            if ($link) {
                $link->update(['last_activity_at' => now()]);

                return $link->sid;
            }

            $sid = (string) Str::uuid();

            SsoSessionClient::create([
                'sso_session_id' => $ssoSessionId,
                'client_id' => $clientId,
                'user_id' => $userId,
                'sid' => $sid,
                'authenticated_at' => now(),
                'last_activity_at' => now(),
            ]);

            return $sid;
        } catch (Throwable) {
            return null;
        }
    }
}
