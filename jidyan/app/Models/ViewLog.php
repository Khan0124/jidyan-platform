<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'viewer_user_id',
        'player_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }
}
