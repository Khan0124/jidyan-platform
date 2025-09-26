<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        parent::register();

        if ($this->app->isProduction()) {
            Telescope::filter(function () {
                return false;
            });
        }
    }

    protected function gate(): void
    {
        Telescope::auth(function ($request) {
            return $request->user()?->hasRole('admin');
        });
    }
}
