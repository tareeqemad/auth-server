<?php

namespace App\Services\OIDC;

use App\Models\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class IdTokenService
{
    public function __construct(
        private readonly JwksService $jwks,
        private readonly ClaimsService $claims,
    ) {}

    public function issue(User $user, string $clientId, array $scopes, ?string $nonce = null, ?string $sid = null): string
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(config('oidc.keys.private')),
            InMemory::file(config('oidc.keys.public')),
        );

        $now = new DateTimeImmutable();
        $ttl = (int) config('oidc.id_token_ttl_minutes', 60);

        $builder = $config->builder()
            ->issuedBy(config('oidc.issuer'))
            ->permittedFor($clientId)
            ->relatedTo((string) $user->id)
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$ttl} minutes"))
            ->withHeader('kid', $this->jwks->getKid());

        if ($nonce !== null && $nonce !== '') {
            $builder = $builder->withClaim('nonce', $nonce);
        }

        if ($sid !== null && $sid !== '') {
            $builder = $builder->withClaim('sid', $sid);
        }

        $authTime = $user->last_login_at?->timestamp ?? $now->getTimestamp();
        $builder = $builder->withClaim('auth_time', $authTime);

        foreach ($this->claims->getClaimsForUser($user, $scopes) as $name => $value) {
            if ($name === 'sub') {
                continue;
            }
            $builder = $builder->withClaim($name, $value);
        }

        return $builder->getToken($config->signer(), $config->signingKey())->toString();
    }
}
