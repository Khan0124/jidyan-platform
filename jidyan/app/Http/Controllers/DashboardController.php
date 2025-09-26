<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('player')) {
            return redirect()->route('dashboard.player.profile.edit');
        }

        if ($user->hasRole('coach')) {
            return redirect()->route('dashboard.coach.search');
        }

        if ($user->hasRole('club_admin')) {
            return redirect()->route('dashboard.club.opportunities.index');
        }

        if ($user->hasRole('agent')) {
            return redirect()->route('dashboard.messages.index');
        }

        if ($user->hasRole('verifier')) {
            return redirect()->route('verifications.index');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('feature-flags.index');
        }

        return redirect()->route('home');
    }
}
