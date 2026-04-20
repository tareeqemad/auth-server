<?php

namespace App\Services\OIDC;

use RuntimeException;

class JwksService
{
    public function getJwks(): array
    {
        return [
            'keys' => [$this->buildJwk()],
        ];
    }

    public function getKid(): string
    {
        return $this->generateKid($this->readPublicKey());
    }

    private function buildJwk(): array
    {
        $pem = $this->readPublicKey();
        $resource = openssl_pkey_get_public($pem);

        if ($resource === false) {
            throw new RuntimeException('Invalid OIDC public key.');
        }

        $details = openssl_pkey_get_details($resource);

        if (! is_array($details) || ! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException('OIDC public key is not an RSA key.');
        }

        return [
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'kid' => $this->generateKid($pem),
            'n' => $this->base64UrlEncode($details['rsa']['n']),
            'e' => $this->base64UrlEncode($details['rsa']['e']),
        ];
    }

    private function readPublicKey(): string
    {
        $path = config('oidc.keys.public');
        $pem = @file_get_contents($path);

        if ($pem === false) {
            throw new RuntimeException("Unable to read OIDC public key at: {$path}");
        }

        return $pem;
    }

    private function generateKid(string $pem): string
    {
        return substr(hash('sha256', $pem), 0, 16);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
