<?php

namespace App\Services\OIDC;

use App\Models\AuditLog;
use App\Models\SsoSession;
use App\Models\SsoSessionClient;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Throwable;

class BackChannelLogoutService
{
    private const EVENT_URI = 'http://schemas.openid.net/event/backchannel-logout';

    public function __construct(private readonly JwksService $jwks)
    {
    }

    /**
     * Send back-channel logout requests to every client attached to the given SSO session.
     *
     * @return array{total:int, success:int, failed:int, skipped:int}
     */
    public function logoutSession(SsoSession $session): array
    {
        $links = SsoSessionClient::with('client')
            ->where('sso_session_id', $session->id)
            ->whereNull('logout_sent_at')
            ->get();

        $stats = ['total' => $links->count(), 'success' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($links as $link) {
            $result = $this->logoutSingle($link);
            $stats[$result]++;
        }

        return $stats;
    }

    /**
     * Send back-channel logout to a specific client within an SSO session.
     * Returns one of: 'success' | 'failed' | 'skipped'.
     */
    public function logoutSingle(SsoSessionClient $link): string
    {
        $client = $link->client;

        if (! $client || ! $client->supportsBackChannelLogout()) {
            $link->update([
                'logout_sent_at' => now(),
                'logout_status' => SsoSessionClient::STATUS_SKIPPED,
                'logout_error' => $client ? 'No back_channel_logout_uri configured' : 'Client not found',
            ]);

            return 'skipped';
        }

        try {
            $token = $this->buildLogoutToken($link);
        } catch (Throwable $e) {
            $link->update([
                'logout_sent_at' => now(),
                'logout_status' => SsoSessionClient::STATUS_FAILED,
                'logout_error' => 'Token build failed: '.$e->getMessage(),
            ]);

            AuditLog::create([
                'user_id' => $link->user_id,
                'client_id' => $link->client_id,
                'event_type' => AuditLog::EVENT_BACKCHANNEL_LOGOUT_FAILED,
                'metadata' => ['reason' => 'token_build_failed', 'error' => $e->getMessage()],
            ]);

            return 'failed';
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->connectTimeout(3)
                ->post($client->back_channel_logout_uri, [
                    'logout_token' => $token,
                ]);

            if ($response->successful()) {
                $link->update([
                    'logout_sent_at' => now(),
                    'logout_status' => SsoSessionClient::STATUS_SUCCESS,
                    'logout_error' => null,
                ]);

                AuditLog::create([
                    'user_id' => $link->user_id,
                    'client_id' => $link->client_id,
                    'event_type' => AuditLog::EVENT_BACKCHANNEL_LOGOUT_SENT,
                    'metadata' => [
                        'sid' => $link->sid,
                        'http_status' => $response->status(),
                        'uri' => $client->back_channel_logout_uri,
                    ],
                ]);

                return 'success';
            }

            $link->update([
                'logout_sent_at' => now(),
                'logout_status' => SsoSessionClient::STATUS_FAILED,
                'logout_error' => 'HTTP '.$response->status().': '.substr($response->body(), 0, 500),
            ]);

            AuditLog::create([
                'user_id' => $link->user_id,
                'client_id' => $link->client_id,
                'event_type' => AuditLog::EVENT_BACKCHANNEL_LOGOUT_FAILED,
                'metadata' => [
                    'sid' => $link->sid,
                    'http_status' => $response->status(),
                    'uri' => $client->back_channel_logout_uri,
                ],
            ]);

            return 'failed';
        } catch (Throwable $e) {
            $link->update([
                'logout_sent_at' => now(),
                'logout_status' => SsoSessionClient::STATUS_FAILED,
                'logout_error' => 'Network error: '.$e->getMessage(),
            ]);

            AuditLog::create([
                'user_id' => $link->user_id,
                'client_id' => $link->client_id,
                'event_type' => AuditLog::EVENT_BACKCHANNEL_LOGOUT_FAILED,
                'metadata' => [
                    'sid' => $link->sid,
                    'error' => $e->getMessage(),
                    'uri' => $client->back_channel_logout_uri,
                ],
            ]);

            return 'failed';
        }
    }

    private function buildLogoutToken(SsoSessionClient $link): string
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(config('oidc.keys.private')),
            InMemory::file(config('oidc.keys.public')),
        );

        $now = new DateTimeImmutable();

        $builder = $config->builder()
            ->issuedBy(config('oidc.issuer'))
            ->permittedFor((string) $link->client_id)
            ->relatedTo((string) $link->user_id)
            ->issuedAt($now)
            ->expiresAt($now->modify('+2 minutes'))
            ->identifiedBy((string) Str::uuid())
            ->withHeader('kid', $this->jwks->getKid())
            ->withHeader('typ', 'logout+jwt')
            ->withClaim('events', [self::EVENT_URI => (object) []])
            ->withClaim('sid', $link->sid);

        return $builder->getToken($config->signer(), $config->signingKey())->toString();
    }
}
