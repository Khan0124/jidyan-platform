<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocaleUpdateRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Update the active locale.
     */
    public function update(LocaleUpdateRequest $request): RedirectResponse
    {
        $locale = $request->validated()['locale'];

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return back()->with('status', __('Language updated.'));
    }
}
