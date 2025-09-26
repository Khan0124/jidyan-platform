<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentLinkRequest;
use App\Models\PlayerAgentLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AgentLinkController extends Controller
{
    public function store(AgentLinkRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', PlayerAgentLink::class);

        $player = $request->user()->playerProfile;

        abort_if($player === null, 403);

        $attributes = [
            'player_id' => $player->getKey(),
            'agent_id' => $request->validated()['agent_id'],
        ];

        $link = PlayerAgentLink::firstOrNew($attributes);

        $created = false;

        if (! $link->exists) {
            $created = true;
        }

        $link->fill([
            'status' => 'pending',
            'requested_by' => $request->user()->getAuthIdentifier(),
            'responded_at' => null,
        ]);

        if ($link->isDirty()) {
            $link->save();
        } elseif ($created) {
            $link->save();
        }

        $link->refresh();

        $message = __('Agent link request sent.');
        $status = $created ? 201 : 200;

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'link' => $link,
            ], $status);
        }

        return back()->with('status', $message);
    }

    public function update(PlayerAgentLink $link, AgentLinkRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $link);

        $status = $request->validated()['status'];
        $user = $request->user();

        if ($user->hasRole('player') && $status !== 'revoked') {
            abort(403);
        }

        if ($user->hasRole('agent') && ! in_array($status, ['active', 'revoked'], true)) {
            abort(403);
        }

        $link->fill([
            'status' => $status,
            'responded_at' => $status === 'pending' ? null : now(),
        ]);

        if ($status === 'pending') {
            $link->requested_by = $user?->getAuthIdentifier();
        }

        if ($link->isDirty()) {
            $link->save();
        }

        $link->refresh();

        $message = __('Agent link updated.');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'link' => $link,
            ]);
        }

        return back()->with('status', $message);
    }
}
