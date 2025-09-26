<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $supported = Config::get('app.supported_locales', [Config::get('app.locale')]);
        $supported = array_values(array_unique(array_filter(Arr::wrap($supported))));

        $locale = null;

        if ($request->hasSession()) {
            $locale = $request->session()->get('locale');
        }

        if (! $locale) {
            $locale = $request->getPreferredLanguage($supported) ?? Config::get('app.locale');
        }

        if (! in_array($locale, $supported, true)) {
            $locale = Config::get('app.locale');
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
