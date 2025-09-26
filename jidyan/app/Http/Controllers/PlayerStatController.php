<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerStatRequest;
use App\Http\Resources\PlayerStatResource;
use App\Models\PlayerStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlayerStatController extends Controller
{
    public function store(PlayerStatRequest $request): RedirectResponse|JsonResponse
    {
        $player = $request->resolvePlayerProfile();
        $stat = $player->stats()->create($request->validated());

        if ($request->wantsJson()) {
            return PlayerStatResource::make($stat->fresh('verifier'))
                ->response()
                ->setStatusCode(201);
        }

        return back()->with('status', __('Stat added successfully.'));
    }

    public function update(PlayerStatRequest $request, PlayerStat $stat): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $stat);

        $stat->update($request->validated());

        if ($request->wantsJson()) {
            return PlayerStatResource::make($stat->fresh('verifier'));
        }

        return back()->with('status', __('Stat updated successfully.'));
    }

    public function destroy(Request $request, PlayerStat $stat): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $stat);

        $stat->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return back()->with('status', __('Stat deleted successfully.'));
    }
}
