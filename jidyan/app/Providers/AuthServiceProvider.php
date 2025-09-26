<?php

namespace App\Providers;

use App\Models\ContentReport;
use App\Models\Opportunity;
use App\Models\PlayerAgentLink;
use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use App\Models\PlayerStat;
use App\Models\Verification;
use App\Policies\ContentReportPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PlayerAgentLinkPolicy;
use App\Policies\PlayerMediaPolicy;
use App\Policies\PlayerProfilePolicy;
use App\Policies\PlayerStatPolicy;
use App\Policies\VerificationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        PlayerProfile::class => PlayerProfilePolicy::class,
        PlayerMedia::class => PlayerMediaPolicy::class,
        PlayerStat::class => PlayerStatPolicy::class,
        Verification::class => VerificationPolicy::class,
        ContentReport::class => ContentReportPolicy::class,
        PlayerAgentLink::class => PlayerAgentLinkPolicy::class,
        Opportunity::class => OpportunityPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-admin-dashboard', fn ($user) => $user->hasRole('admin'));
    }
}
