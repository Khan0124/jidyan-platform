<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortlist_id',
        'player_id',
        'note',
    ];

    public function shortlist()
    {
        return $this->belongsTo(Shortlist::class);
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }
}
