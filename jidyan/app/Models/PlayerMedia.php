<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'type',
        'provider',
        'original_filename',
        'path',
        'hls_path',
        'poster_path',
        'duration_sec',
        'quality_label',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function reports()
    {
        return $this->morphMany(ContentReport::class, 'reportable');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
