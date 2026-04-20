<?php

namespace App\Services\OIDC;

use App\Models\User;

class ClaimsService
{
    public function getClaimsForUser(User $user, array $scopes): array
    {
        $claims = ['sub' => (string) $user->id];

        if (in_array('email', $scopes, true)) {
            $claims['email'] = $user->email;
            $claims['email_verified'] = $user->email_verified_at !== null;
        }

        if (in_array('profile', $scopes, true)) {
            $claims['name'] = $user->full_name;

            $parts = preg_split('/\s+/', trim((string) $user->full_name), 2);
            $claims['given_name'] = $parts[0] ?? '';
            $claims['family_name'] = $parts[1] ?? '';
        }

        if (in_array('phone', $scopes, true) && $user->phone) {
            $claims['phone_number'] = $user->phone;
            $claims['phone_number_verified'] = false;
        }

        return $claims;
    }
}
