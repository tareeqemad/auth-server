<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PalestinianNationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = trim((string) $value);

        if (strlen($id) !== 9 || ! ctype_digit($id)) {
            $fail('رقم الهوية يجب أن يكون 9 أرقام.');

            return;
        }

        if (! self::isValid($id)) {
            $fail('رقم الهوية غير صحيح.');
        }
    }

    /**
     * Validate a Palestinian national ID using the Luhn-like checksum.
     * Mirrors the JS validator so client-side + server-side agree.
     */
    public static function isValid(string $id): bool
    {
        $id = trim($id);

        if (strlen($id) !== 9 || ! ctype_digit($id)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $step = (int) $id[$i] * (($i % 2) + 1);
            $sum += $step > 9 ? $step - 9 : $step;
        }

        return $sum % 10 === 0;
    }

    /**
     * Mask a national ID for safe logging/display (e.g. "400****865").
     */
    public static function mask(?string $id): string
    {
        if (! $id || strlen($id) !== 9) {
            return '—';
        }

        return substr($id, 0, 3).'****'.substr($id, -3);
    }
}
