<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('short_locale')) {
    function short_locale(?string $locale = null): string
    {
        return Str::of($locale ?? app()->getLocale())->substr(0, 2);
    }
}
