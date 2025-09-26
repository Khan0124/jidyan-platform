<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PlayerProfile extends Model
{
    use HasFactory;

    public const AVAILABILITY_OPTIONS = [
        'available',
        'contracted',
        'injured',
        'unknown',
    ];

    protected $table = 'profiles_players';

    protected $fillable = [
        'user_id',
        'dob',
        'nationality',
        'city',
        'country',
        'height_cm',
        'weight_kg',
        'position',
        'preferred_foot',
        'current_club',
        'previous_clubs',
        'bio',
        'injuries',
        'achievements',
        'visibility',
        'availability',
        'available_from',
        'preferred_roles',
        'verified_identity_at',
        'verified_academy_at',
        'last_active_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'previous_clubs' => 'array',
        'injuries' => 'array',
        'achievements' => 'array',
        'verified_identity_at' => 'datetime',
        'verified_academy_at' => 'datetime',
        'available_from' => 'date',
        'preferred_roles' => 'array',
        'last_active_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            $profile->availability ??= 'unknown';
            $profile->last_active_at ??= now();
        });

        static::saving(function (self $profile) {
            $profile->searchable_text = $profile->generateSearchableText();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(PlayerMedia::class, 'player_id');
    }

    public function stats()
    {
        return $this->hasMany(PlayerStat::class, 'player_id');
    }

    public function reports()
    {
        return $this->morphMany(ContentReport::class, 'reportable');
    }

    public function scopeVisible(Builder $query)
    {
        return $query->where('visibility', 'public');
    }

    public function hasBadge(string $badge): bool
    {
        return match ($badge) {
            'verified_identity' => (bool) $this->verified_identity_at,
            'verified_academy' => (bool) $this->verified_academy_at,
            default => false,
        };
    }

    public function availabilityLabel(): string
    {
        return match ($this->availability) {
            'available' => __('Available now'),
            'contracted' => __('Under contract'),
            'injured' => __('Injured / rehab'),
            default => __('Availability unknown'),
        };
    }

    public function refreshSearchIndex(): void
    {
        $this->forceFill([
            'searchable_text' => $this->generateSearchableText(),
        ])->saveQuietly();
    }

    protected function generateSearchableText(): string
    {
        $parts = $this->searchableParts();

        return $parts
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(' ');
    }

    protected function searchableParts(): Collection
    {
        return collect([
            $this->resolveUserName(),
            $this->position,
            $this->preferred_foot,
            $this->current_club,
            $this->city,
            $this->country,
            $this->bio,
            $this->previous_clubs,
            $this->achievements,
            $this->injuries,
        ])->flatMap(function ($value) {
            if (is_array($value)) {
                return array_filter($value, fn ($item) => filled($item));
            }

            return [$value];
        });
    }

    protected function resolveUserName(): ?string
    {
        if ($this->relationLoaded('user')) {
            return optional($this->getRelation('user'))->name;
        }

        return $this->user()->select('id', 'name')->value('name');
    }
}
