<?php

namespace App\Policies;

use App\Models\ContentReport;
use App\Models\User;

class ContentReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['verifier', 'admin']);
    }

    public function review(User $user, ContentReport $report): bool
    {
        return $user->hasAnyRole(['verifier', 'admin']);
    }
}
