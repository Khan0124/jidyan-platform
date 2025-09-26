<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shortlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'title',
        'notes',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }

    public function items()
    {
        return $this->hasMany(ShortlistItem::class);
    }
}
