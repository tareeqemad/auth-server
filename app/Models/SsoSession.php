<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SsoSession extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'mfa_verified',
        'last_activity_at',
        'expires_at',
        'revoked',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'mfa_verified' => 'boolean',
            'revoked' => 'boolean',
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(SsoSessionClient::class, 'sso_session_id');
    }

    public function isActive(): bool
    {
        return ! $this->revoked && $this->expires_at->isFuture();
    }
}
