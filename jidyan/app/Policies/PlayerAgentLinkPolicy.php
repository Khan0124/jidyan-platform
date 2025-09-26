<?php

namespace App\Policies;

use App\Models\PlayerAgentLink;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayerAgentLinkPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->hasRole('player') && $user->playerProfile !== null;
    }

    public function update(User $user, PlayerAgentLink $link): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('player') && $user->playerProfile?->getKey() === $link->player_id) {
            return true;
        }

        if ($user->hasRole('agent') && $user->agent?->getKey() === $link->agent_id) {
            return true;
        }

        return false;
    }
}
