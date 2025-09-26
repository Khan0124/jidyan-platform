<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'preferred_locale'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $user) {
            $user->playerProfile?->refreshSearchIndex();
        });
    }

    public function playerProfile()
    {
        return $this->hasOne(PlayerProfile::class, 'user_id');
    }

    public function coach()
    {
        return $this->hasOne(Coach::class);
    }

    public function clubAdmin()
    {
        return $this->hasOne(ClubAdmin::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function reports()
    {
        return $this->hasMany(ContentReport::class, 'reporter_user_id');
    }

    public function resolvedReports()
    {
        return $this->hasMany(ContentReport::class, 'resolved_by');
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->phone;
    }
}
