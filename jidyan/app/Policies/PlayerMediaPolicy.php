<?php

namespace App\Policies;

use App\Models\PlayerMedia;
use App\Models\User;

class PlayerMediaPolicy
{
    public function delete(User $user, PlayerMedia $media): bool
    {
        return $user->id === $media->player->user_id || $user->hasRole('admin');
    }
}
