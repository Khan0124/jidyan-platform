<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'player_id',
        'agent_id',
        'media_id',
        'note',
        'status',
        'reviewed_by_user_id',
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function media()
    {
        return $this->belongsTo(PlayerMedia::class, 'media_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
