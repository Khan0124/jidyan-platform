<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Jobs\DispatchApplicationNotificationJob;
use App\Models\Application;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View|JsonResponse
    {
        $applications = Application::query()
            ->with([
                'opportunity.club',
                'player.user',
                'player.media' => fn ($query) => $query->where('status', 'ready')->latest(),
                'player.stats' => fn ($query) => $query->latest('season')->with('verifier'),
                'media',
            ])
            ->latest()
            ->paginate(20);

        if (request()->wantsJson()) {
            return ApplicationResource::collection($applications);
        }

        return view('dashboard.club.applications.index', compact('applications'));
    }

    public function store(ApplicationRequest $request, Opportunity $opportunity): RedirectResponse|JsonResponse
    {
        $player = $request->user()->playerProfile ?? $request->user()->playerProfile()->create();
        $data = $request->validated();
        $data['player_id'] = $player->getKey();
        $application = $opportunity->applications()->create($data);

        DispatchApplicationNotificationJob::dispatch($application->id);

        if ($request->wantsJson()) {
            return ApplicationResource::make($application->loadMissing([
                'opportunity.club',
                'player.user',
                'player.media' => fn ($query) => $query->where('status', 'ready')->latest(),
                'player.stats' => fn ($query) => $query->latest('season')->with('verifier'),
                'media',
            ]))->response()->setStatusCode(201);
        }

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with('status', __('Application submitted successfully.'));
    }

    public function updateStatus(ApplicationRequest $request, Application $application): RedirectResponse|JsonResponse
    {
        $application->loadMissing('opportunity.club');

        $this->authorize('update', $application);
        $application->update($request->validated());

        if ($request->wantsJson()) {
            return ApplicationResource::make($application->loadMissing([
                'opportunity.club',
                'player.user',
                'player.media' => fn ($query) => $query->where('status', 'ready')->latest(),
                'player.stats' => fn ($query) => $query->latest('season')->with('verifier'),
                'media',
            ]));
        }

        return back()->with('status', __('Application updated.'));
    }
}
