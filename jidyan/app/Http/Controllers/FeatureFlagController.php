<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeatureFlagUpdateRequest;
use App\Models\FeatureFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeatureFlagController extends Controller
{
    public function index(): View
    {
        $flags = FeatureFlag::query()->orderBy('key')->get();

        return view('dashboard.admin.feature-flags.index', compact('flags'));
    }

    public function update(FeatureFlagUpdateRequest $request, FeatureFlag $featureFlag): RedirectResponse
    {
        $featureFlag->update($request->validated());

        return redirect()
            ->route('feature-flags.index')
            ->with('status', __('messages.Feature flag updated.'));
    }
}
