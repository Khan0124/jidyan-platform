<?php

namespace App\Policies;

use App\Models\PlayerStat;
use App\Models\User;

class PlayerStatPolicy
{
    public function update(User $user, PlayerStat $stat): bool
    {
        $ownerId = $stat->player?->user_id;

        if (! $ownerId) {
            return $user->hasRole('admin');
        }

        return $user->id === $ownerId || $user->hasRole('admin');
    }

    public function delete(User $user, PlayerStat $stat): bool
    {
        return $this->update($user, $stat);
    }
}
