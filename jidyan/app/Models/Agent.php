<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_no',
        'agency_name',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function players()
    {
        return $this->belongsToMany(PlayerProfile::class, 'player_agent_links', 'agent_id', 'player_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }
}
