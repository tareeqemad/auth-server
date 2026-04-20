<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    private const CACHE_KEY = 'app:settings:all';
    private const CACHE_TTL = 3600;

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all_cached();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        return $all[$key];
    }

    public static function set(string $key, mixed $value): void
    {
        $model = static::firstOrNew(['key' => $key]);
        $model->value = is_array($value) ? json_encode($value) : (string) $value;
        $model->save();

        Cache::forget(self::CACHE_KEY);
    }

    public static function all_cached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::all()
                ->mapWithKeys(fn (Setting $s) => [$s->key => self::castValue($s)])
                ->toArray();
        });
    }

    private static function castValue(Setting $s): mixed
    {
        return match ($s->type) {
            'boolean' => filter_var($s->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $s->value,
            'json' => json_decode($s->value ?? '[]', true),
            default => $s->value,
        };
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
