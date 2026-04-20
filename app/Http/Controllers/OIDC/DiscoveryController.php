<?php

namespace App\Http\Controllers\OIDC;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DiscoveryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $issuer = config('oidc.issuer');

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'userinfo_endpoint' => $issuer.'/oauth/userinfo',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'end_session_endpoint' => $issuer.'/oauth/logout',
            'scopes_supported' => array_keys(config('oidc.scopes')),
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query', 'fragment'],
            'grant_types_supported' => [
                'authorization_code',
                'refresh_token',
                'client_credentials',
                'password',
                'urn:ietf:params:oauth:grant-type:device_code',
            ],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post'],
            'claims_supported' => config('oidc.claims_supported'),
            'code_challenge_methods_supported' => ['S256', 'plain'],
            'claim_types_supported' => ['normal'],
        ]);
    }
}
