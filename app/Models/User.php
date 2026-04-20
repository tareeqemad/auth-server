<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    /**
     * Admin panel roles (users with any of these can access /admin).
     */
    public const ADMIN_ROLES = [
        'super_admin',
        'user_manager',
        'client_manager',
        'viewer',
    ];

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(self::ADMIN_ROLES);
    }

    /**
     * Indicates the ID is not auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'full_name',
        'phone',
        'is_active',
        'mfa_secret',
        'sms_2fa_enabled',
        'sms_2fa_enabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'mfa_secret' => 'encrypted',
            'is_active' => 'boolean',
            'sms_2fa_enabled' => 'boolean',
            'sms_2fa_enabled_at' => 'datetime',
        ];
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function ssoSessions(): HasMany
    {
        return $this->hasMany(SsoSession::class);
    }

    public function systemLinks(): HasMany
    {
        return $this->hasMany(UserSystemLink::class);
    }
}
