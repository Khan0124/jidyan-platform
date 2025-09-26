<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpportunityRequest;
use App\Http\Resources\OpportunityResource;
use App\Models\Club;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $club = $request->user()?->clubAdmin?->club;
        $opportunities = Opportunity::query()
            ->with(['club'])
            ->withCount('applications')
            ->when($club, fn ($query) => $query->where('club_id', $club->id))
            ->when(! $club, fn ($query) => $query->publiclyVisible())
            ->latest()
            ->paginate(15);

        if ($request->wantsJson()) {
            return OpportunityResource::collection($opportunities);
        }

        return view('dashboard.club.opportunities.index', compact('opportunities'));
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $opportunity->loadMissing(['club']);

        return view('dashboard.club.opportunities.show', compact('opportunity'));
    }

    public function create(): View
    {
        return view('dashboard.club.opportunities.create');
    }

    public function store(OpportunityRequest $request): RedirectResponse|JsonResponse
    {
        $club = $request->user()->clubAdmin?->club ?? Club::firstOrFail();
        $data = collect($request->validated())->except('requirements_json')->toArray();
        $opportunity = $club->opportunities()->create($data);

        if ($request->wantsJson()) {
            return OpportunityResource::make($opportunity->fresh('club'))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('dashboard.club.opportunities.edit', $opportunity)->with('status', __('Opportunity created successfully.'));
    }

    public function edit(Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        return view('dashboard.club.opportunities.edit', compact('opportunity'));
    }

    public function update(OpportunityRequest $request, Opportunity $opportunity): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $opportunity);
        $data = collect($request->validated())->except('requirements_json')->toArray();
        $opportunity->update($data);

        if ($request->wantsJson()) {
            return OpportunityResource::make($opportunity->fresh('club'));
        }

        return back()->with('status', __('Opportunity updated.'));
    }
}
