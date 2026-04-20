<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsOtp extends Model
{
    public const PURPOSE_PASSWORD_RESET = 'password_reset';
    public const PURPOSE_LOGIN_2FA = 'login_2fa';
    public const PURPOSE_PHONE_VERIFY = 'phone_verify';

    protected $fillable = [
        'user_id',
        'phone',
        'code',
        'purpose',
        'expires_at',
        'used_at',
        'attempts',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isUsed();
    }
}
