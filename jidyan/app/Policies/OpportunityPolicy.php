<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function view(User $user, Opportunity $opportunity): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('club_admin') && $user->clubAdmin?->club_id === $opportunity->club_id) {
            return true;
        }

        if ($user->hasRole('coach') && $user->coach?->club_id === $opportunity->club_id) {
            return true;
        }

        return false;
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('club_admin')
            && $user->clubAdmin?->club_id === $opportunity->club_id;
    }
}
