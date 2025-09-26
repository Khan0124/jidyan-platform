<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'title',
        'slug',
        'description',
        'requirements',
        'location_city',
        'location_country',
        'deadline_at',
        'status',
        'visibility',
    ];

    protected $casts = [
        'requirements' => 'array',
        'deadline_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(ContentReport::class, 'reportable');
    }

    protected static function booted(): void
    {
        static::creating(function (self $opportunity) {
            if (! $opportunity->slug) {
                $opportunity->slug = Str::slug($opportunity->title.'-'.Str::random(6));
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('visibility', 'public')
            ->published()
            ->where(function (Builder $builder) {
                $builder->whereNull('deadline_at')
                    ->orWhere('deadline_at', '>=', now());
            });
    }

    public function isPubliclyVisible(): bool
    {
        return $this->visibility === 'public'
            && $this->status === 'published'
            && (! $this->deadline_at || $this->deadline_at->isFuture());
    }
}
