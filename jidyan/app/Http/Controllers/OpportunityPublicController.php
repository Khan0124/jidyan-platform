<?php

namespace App\Http\Controllers;

use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityPublicController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'country' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'club_id' => ['nullable', 'integer', 'exists:clubs,id'],
            'deadline_from' => ['nullable', 'date'],
            'deadline_to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Opportunity::query()
            ->publiclyVisible()
            ->with('club')
            ->withCount('applications');

        $query->when($filters['country'] ?? null, fn ($builder, $country) => $builder->where('location_country', $country));
        $query->when($filters['city'] ?? null, fn ($builder, $city) => $builder->where('location_city', $city));
        $query->when($filters['club_id'] ?? null, fn ($builder, $clubId) => $builder->where('club_id', $clubId));
        $query->when($filters['deadline_from'] ?? null, fn ($builder, $from) => $builder->whereDate('deadline_at', '>=', $from));
        $query->when($filters['deadline_to'] ?? null, fn ($builder, $to) => $builder->whereDate('deadline_at', '<=', $to));

        if (! empty($filters['q'])) {
            $term = mb_strtolower($filters['q']);
            $query->where(function ($builder) use ($term) {
                $builder->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
            });
        }

        /** @var LengthAwarePaginator $opportunities */
        $opportunities = $query
            ->latest('deadline_at')
            ->paginate(12)
            ->appends($filters);

        if ($request->wantsJson()) {
            return OpportunityResource::collection($opportunities);
        }

        return view('opportunities.index', [
            'opportunities' => $opportunities,
            'filters' => array_filter($filters, fn ($value) => $value !== null && $value !== ''),
        ]);
    }

    public function show(Request $request, Opportunity $opportunity): View
    {
        if (! $opportunity->isPubliclyVisible()) {
            abort(404);
        }

        $opportunity->loadMissing('club');

        $playerMedia = collect();

        if ($request->user()?->playerProfile) {
            $playerMedia = $request->user()->playerProfile
                ->media()
                ->where('status', 'ready')
                ->where('type', 'video')
                ->orderByDesc('created_at')
                ->get(['id', 'original_filename']);
        }

        return view('opportunities.show', [
            'opportunity' => $opportunity,
            'playerMedia' => $playerMedia,
        ]);
    }
}
