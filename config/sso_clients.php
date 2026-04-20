<?php

/*
 * Registry of the 5 client systems the IdP serves.
 * Names here are used as the unique identifier for idempotent sync.
 * Redirect URIs are env-configurable — update .env per environment (dev/staging/prod).
 */

return [
    [
        'key' => 'system_a',
        'name' => 'SSO :: System A (CodeIgniter 3 / MySQL)',
        'display_name' => 'النظام الأول',
        'description' => 'CodeIgniter 3 · MySQL',
        'color' => '#2563eb',
        'launch_url' => env('SYSTEM_A_URL', 'http://localhost:8001'),
        'redirect_uris' => [
            env('SYSTEM_A_REDIRECT_URI', 'http://localhost:8001/auth/callback'),
        ],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'confidential' => true,
    ],
    [
        'key' => 'system_b',
        'name' => 'SSO :: System B (CodeIgniter 4 / MySQL)',
        'display_name' => 'النظام الثاني',
        'description' => 'CodeIgniter 4 · MySQL',
        'color' => '#059669',
        'launch_url' => env('SYSTEM_B_URL', 'http://localhost:8002'),
        'redirect_uris' => [
            env('SYSTEM_B_REDIRECT_URI', 'http://localhost:8002/auth/callback'),
        ],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'confidential' => true,
    ],
    [
        'key' => 'system_c',
        'name' => 'SSO :: System C (CodeIgniter 4 / Oracle)',
        'display_name' => 'النظام الثالث',
        'description' => 'CodeIgniter 4 · Oracle',
        'color' => '#d97706',
        'launch_url' => env('SYSTEM_C_URL', 'http://localhost:8003'),
        'redirect_uris' => [
            env('SYSTEM_C_REDIRECT_URI', 'http://localhost:8003/auth/callback'),
        ],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'confidential' => true,
    ],
    [
        'key' => 'system_d',
        'name' => 'SSO :: System D (Laravel / MySQL)',
        'display_name' => 'النظام الرابع',
        'description' => 'Laravel · MySQL',
        'color' => '#dc2626',
        'launch_url' => env('SYSTEM_D_URL', 'http://localhost:8004'),
        'redirect_uris' => [
            env('SYSTEM_D_REDIRECT_URI', 'http://localhost:8004/auth/callback'),
        ],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'confidential' => true,
    ],
    [
        'key' => 'system_e',
        'name' => 'SSO :: System E (Next.js + Nest.js / PostgreSQL)',
        'display_name' => 'النظام الخامس',
        'description' => 'Next.js + Nest.js · PostgreSQL',
        'color' => '#7c3aed',
        'launch_url' => env('SYSTEM_E_URL', 'http://localhost:8005'),
        'redirect_uris' => [
            env('SYSTEM_E_REDIRECT_URI', 'http://localhost:8005/auth/callback'),
        ],
        'grant_types' => ['authorization_code', 'refresh_token'],
        'confidential' => true,
    ],
];
