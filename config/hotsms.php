<?php

return [
    'enabled' => env('HOTSMS_ENABLED', true),
    'username' => env('HOTSMS_USERNAME'),
    'password' => env('HOTSMS_PASSWORD'),
    'api_token' => env('HOTSMS_API_TOKEN'),
    'sender' => env('HOTSMS_SENDER', 'GEDCO'),
    'api_url' => rtrim(env('HOTSMS_API_URL', 'http://hotsms.ps'), '/'),
    'timeout' => (int) env('HOTSMS_TIMEOUT', 10),

    /*
    | Default country code (without +). Palestinian numbers = 970.
    | Used when user enters a local number without country code.
    */
    'default_country_code' => env('HOTSMS_COUNTRY_CODE', '970'),

    /*
    | Type of SMS encoding:
    | 0 = GSM (160 chars, English/basic only)
    | 1 = Unicode (70 chars × 4)
    | 2 = UTF-8 (70 chars, supports Arabic) — default for Arabic messages
    */
    'default_type' => 2,

    /*
    | OTP settings
    */
    'otp' => [
        'length' => 6,
        'ttl_minutes' => 10,
        'max_attempts' => 5,
        'cooldown_seconds' => 60,
    ],
];
