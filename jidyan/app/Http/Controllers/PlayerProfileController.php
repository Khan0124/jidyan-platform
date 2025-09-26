<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerProfileRequest;
use App\Http\Resources\PlayerProfileResource;
use App\Models\PlayerProfile;
use App\Models\ViewLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerProfileController extends Controller
{
    public function show(Request $request, PlayerProfile $player): View|JsonResponse
    {
        $player->increment('view_count');
        $player->load([
            'user',
            'media' => fn ($query) => $query->orderBy('created_at'),
            'stats' => fn ($query) => $query->latest('season')->with('verifier'),
        ]);

        $viewer = $request->user() ?? $request->user('sanctum');

        ViewLog::create([
            'viewer_user_id' => $viewer?->getAuthIdentifier(),
            'player_id' => $player->getKey(),
            'viewed_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return PlayerProfileResource::make($player);
        }

        return view('players.show', compact('player'));
    }

    public function edit(Request $request): View
    {
        $player = $request->user()->playerProfile ?? $request->user()->playerProfile()->create();
        $player->loadMissing([
            'media' => fn ($query) => $query->latest(),
            'stats' => fn ($query) => $query->latest('season')->with('verifier'),
        ]);

        return view('dashboard.player.profile', [
            'player' => $player,
        ]);
    }

    public function update(PlayerProfileRequest $request): RedirectResponse|JsonResponse
    {
        /** @var Authenticatable&\App\Models\User $user */
        $user = $request->user();
        $profile = $user->playerProfile()->firstOrCreate([]);
        $profile->fill($request->validated());
        $profile->last_active_at = now();
        $profile->save();

        if ($request->wantsJson()) {
            return PlayerProfileResource::make(
                $profile->fresh([
                    'user',
                    'media' => fn ($query) => $query->latest(),
                    'stats' => fn ($query) => $query->latest('season')->with('verifier'),
                ])
            );
        }

        return redirect()->route('dashboard.player.profile.edit')->with('status', __('Profile updated successfully.'));
    }
}
