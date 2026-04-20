<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SsoSessionClient extends Model
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'sso_session_id',
        'client_id',
        'user_id',
        'sid',
        'authenticated_at',
        'last_activity_at',
        'logout_sent_at',
        'logout_status',
        'logout_error',
    ];

    protected function casts(): array
    {
        return [
            'authenticated_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logout_sent_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SsoSession::class, 'sso_session_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
