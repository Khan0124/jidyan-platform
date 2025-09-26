<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerProfileResource;
use App\Models\PlayerProfile;
use App\Queries\PlayerSearch as PlayerSearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlayerSearchController extends Controller
{
    public function __invoke(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'position' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'preferred_foot' => ['nullable', 'string'],
            'min_height' => ['nullable', 'integer'],
            'max_height' => ['nullable', 'integer'],
            'min_weight' => ['nullable', 'integer'],
            'max_weight' => ['nullable', 'integer'],
            'min_age' => ['nullable', 'integer', 'min:10', 'max:60'],
            'max_age' => ['nullable', 'integer', 'min:10', 'max:60'],
            'has_video' => ['nullable'],
            'badge' => ['nullable', 'string'],
            'availability' => ['nullable', Rule::in(PlayerProfile::AVAILABILITY_OPTIONS)],
            'last_active' => ['nullable', Rule::in([7, 30, 90])],
            'keywords' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['newest', 'most_viewed', 'top_rated', 'recently_active', 'relevance'])],
        ]);

        if (empty($filters['keywords']) && ! empty($filters['q'])) {
            $filters['keywords'] = $filters['q'];
        }

        unset($filters['q']);

        $sort = $filters['sort'] ?? 'newest';
        $query = PlayerSearchQuery::build($filters, $sort);

        if ($request->wantsJson()) {
            return PlayerProfileResource::collection(
                $query->paginate(20)->appends($filters)
            );
        }

        return view('dashboard.coach.search', [
            'filters' => array_filter($filters, fn ($value) => $value !== null && $value !== ''),
            'sort' => $sort,
        ]);
    }
}
