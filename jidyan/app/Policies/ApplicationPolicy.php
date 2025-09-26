<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function update(User $user, Application $application): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $clubId = $application->opportunity->club_id;

        if ($user->hasRole('club_admin') && $user->clubAdmin?->club_id === $clubId) {
            return true;
        }

        if ($user->hasRole('coach') && $user->coach?->club_id === $clubId) {
            return true;
        }

        return false;
    }
}
