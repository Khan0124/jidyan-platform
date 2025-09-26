<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;

class VerificationPolicy
{
    public function verify(User $user, Verification $verification): bool
    {
        return $user->hasAnyRole(['verifier', 'admin']);
    }
}
