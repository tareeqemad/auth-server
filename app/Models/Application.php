<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\Client;

class Application extends Client
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'secret',
        'provider',
        'redirect_uris',
        'grant_types',
        'revoked',
        'user_id',
        'display_name_ar',
        'display_name_en',
        'description',
        'color',
        'launch_url',
        'logo_url',
        'is_first_party',
    ];

    protected $hidden = [
        'secret',
    ];

    protected function casts(): array
    {
        return [
            'redirect_uris' => 'array',
            'grant_types' => 'array',
            'revoked' => 'boolean',
            'is_first_party' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        return $this->display_name_ar
            ?: $this->display_name_en
            ?: $this->name
            ?: '';
    }

    public function initial(): string
    {
        $name = $this->displayName();

        return $name !== '' ? mb_substr($name, 0, 1) : '';
    }

    public function statusLabel(): string
    {
        return $this->revoked ? 'معطّل' : 'نشط';
    }

    public function statusColor(): string
    {
        return $this->revoked ? 'rose' : 'emerald';
    }

    public function firstRedirectUri(): ?string
    {
        $uris = $this->redirect_uris ?? [];

        return is_array($uris) && count($uris) > 0 ? $uris[0] : null;
    }
}
