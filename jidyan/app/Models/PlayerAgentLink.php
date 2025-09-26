<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAgentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'agent_id',
        'status',
        'requested_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
