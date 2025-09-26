<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'season',
        'matches',
        'goals',
        'assists',
        'notes',
        'verified_by_user_id',
    ];

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
