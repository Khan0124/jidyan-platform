<?php

namespace App\Providers;

use App\Models\PlayerProfile;
use App\Policies\PlayerProfilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(PlayerProfile::class, PlayerProfilePolicy::class);
    }
}
