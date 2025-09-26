<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! optional($user->playerProfile)->verified_identity_at) {
            abort(403, __('Your profile must be verified before accessing this area.'));
        }

        return $next($request);
    }
}
