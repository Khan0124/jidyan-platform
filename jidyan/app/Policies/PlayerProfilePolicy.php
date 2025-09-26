<?php

namespace App\Policies;

use App\Models\PlayerProfile;
use App\Models\User;

class PlayerProfilePolicy
{
    public function update(User $user, PlayerProfile $profile): bool
    {
        return $user->id === $profile->user_id || $user->hasRole('admin');
    }

    public function view(User $user, PlayerProfile $profile): bool
    {
        if ($profile->visibility === 'public') {
            return true;
        }

        return $user->id === $profile->user_id || $user->hasAnyRole(['coach', 'club_admin', 'agent']);
    }
}
