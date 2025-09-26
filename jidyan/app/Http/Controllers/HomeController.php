<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\PlayerProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredPlayers = PlayerProfile::query()
            ->visible()
            ->with(['user', 'media' => fn ($query) => $query->where('type', 'video')->where('status', 'ready')])
            ->latest()
            ->take(12)
            ->get();

        $opportunities = Opportunity::query()
            ->publiclyVisible()
            ->latest()
            ->take(6)
            ->get();

        return view('welcome', [
            'featuredPlayers' => $featuredPlayers,
            'opportunities' => $opportunities,
        ]);
    }
}
