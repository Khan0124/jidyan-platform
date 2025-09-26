<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'enabled', 'description'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        $forget = function (FeatureFlag $flag): void {
            Cache::forget("feature_flags:{$flag->key}");
        };

        static::saved($forget);
        static::deleted($forget);
    }

    public static function isEnabled(string $key, bool $default = false): bool
    {
        return Cache::remember("feature_flags:{$key}", now()->addMinutes(5), function () use ($key, $default) {
            return optional(static::query()->where('key', $key)->first())->enabled ?? $default;
        });
    }
}
