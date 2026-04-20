<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_LOGIN_SUCCESS = 'login_success';
    public const EVENT_LOGIN_FAILED = 'login_failed';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    public const EVENT_PASSWORD_RESET_COMPLETED = 'password_reset_completed';
    public const EVENT_PASSWORD_CHANGED = 'password_changed';
    public const EVENT_MFA_ENABLED = 'mfa_enabled';
    public const EVENT_MFA_DISABLED = 'mfa_disabled';
    public const EVENT_MFA_CHALLENGE_SUCCESS = 'mfa_challenge_success';
    public const EVENT_MFA_CHALLENGE_FAILED = 'mfa_challenge_failed';
    public const EVENT_ACCOUNT_LOCKED = 'account_locked';
    public const EVENT_ACCOUNT_UNLOCKED = 'account_unlocked';
    public const EVENT_ACCOUNT_LOCKED_BY_ADMIN = 'account_locked_by_admin';
    public const EVENT_ACCOUNT_UNLOCKED_BY_ADMIN = 'account_unlocked_by_admin';
    public const EVENT_PASSWORD_REUSE_BLOCKED = 'password_reuse_blocked';
    public const EVENT_TOKEN_ISSUED = 'token_issued';
    public const EVENT_TOKEN_REVOKED = 'token_revoked';
    public const EVENT_CONSENT_GRANTED = 'consent_granted';
    public const EVENT_CONSENT_DENIED = 'consent_denied';
    public const EVENT_SLO_INITIATED = 'slo_initiated';
    public const EVENT_BACKCHANNEL_LOGOUT_SENT = 'backchannel_logout_sent';
    public const EVENT_BACKCHANNEL_LOGOUT_FAILED = 'backchannel_logout_failed';
    public const EVENT_RP_LOGOUT_REQUESTED = 'rp_logout_requested';

    protected $fillable = [
        'user_id',
        'event_type',
        'email',
        'ip_address',
        'user_agent',
        'client_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
