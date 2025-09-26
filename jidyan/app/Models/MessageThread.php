<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
    ];

    public function participants()
    {
        return $this->hasMany(MessageThreadParticipant::class, 'thread_id')->with('user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }
}
