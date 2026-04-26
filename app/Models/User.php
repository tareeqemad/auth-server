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
        'failed_login_attempts',
        'locked_until',
        'locked_reason',
        'locked_by_admin_id',
        'last_failed_login_at',
        'national_id',
        'employee_number',
        'source',
        'needs_id_linking',
        'job_title',
        'department',
        'directorate',
        'governorate',
    ];

    public const SOURCE_HR_MASTER = 'hr_master';
    public const SOURCE_EXTERNAL = 'external';
    public const SOURCE_SELF_REGISTERED = 'self_registered';

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
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'needs_id_linking' => 'boolean',
        ];
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function maskedNationalId(): string
    {
        return \App\Rules\PalestinianNationalId::mask($this->national_id);
    }

    public function lockReasonLabel(): ?string
    {
        if (! $this->isLocked()) {
            return null;
        }

        return $this->locked_by_admin_id
            ? ($this->locked_reason ?: 'تم الحظر بواسطة المشرف')
            : ($this->locked_reason ?: 'تجاوز محاولات الدخول الفاشلة');
    }

    public function lockedByAdmin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'locked_by_admin_id');
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
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
