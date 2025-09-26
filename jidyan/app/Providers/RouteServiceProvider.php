<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        parent::boot();

        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
