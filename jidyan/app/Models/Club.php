<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'city',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function administrators()
    {
        return $this->hasMany(ClubAdmin::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
