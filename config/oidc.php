<?php

return [
    'issuer' => rtrim(env('OIDC_ISSUER', env('APP_URL', 'http://localhost')), '/'),

    'id_token_ttl_minutes' => (int) env('OIDC_ID_TOKEN_TTL', 60),

    'scopes' => [
        'openid' => 'Verify your identity',
        'profile' => 'Access your basic profile information',
        'email' => 'Access your email address',
        'phone' => 'Access your phone number',
        'national_id' => 'Access your Palestinian national ID (used to match you with your record in each client system)',
    ],

    'claims_supported' => [
        'sub', 'iss', 'aud', 'exp', 'iat', 'auth_time', 'nonce',
        'email', 'email_verified',
        'name', 'given_name', 'family_name',
        'phone_number', 'phone_number_verified',
        'national_id',
    ],

    'keys' => [
        'private' => env('OIDC_PRIVATE_KEY_PATH', storage_path('oauth-private.key')),
        'public' => env('OIDC_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),
    ],
];
