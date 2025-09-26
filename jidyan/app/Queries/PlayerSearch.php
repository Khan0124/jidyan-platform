<?php

namespace App\Queries;

use App\Models\PlayerProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PlayerSearch
{
    public static function build(array $filters = [], string $sort = 'newest'): Builder
    {
        $query = PlayerProfile::query()
            ->select('profiles_players.*')
            ->visible()
            ->with([
                'user',
                'media' => fn ($mediaQuery) => $mediaQuery
                    ->where('type', 'video')
                    ->where('status', 'ready')
                    ->orderByDesc('created_at'),
                'stats',
            ]);

        if ($position = Arr::get($filters, 'position')) {
            $query->where('position', $position);
        }

        if ($city = Arr::get($filters, 'city')) {
            $query->where('city', $city);
        }

        if ($country = Arr::get($filters, 'country')) {
            $query->where('country', $country);
        }

        if ($preferredFoot = Arr::get($filters, 'preferred_foot')) {
            $query->where('preferred_foot', $preferredFoot);
        }

        if ($minHeight = Arr::get($filters, 'min_height')) {
            $query->where('height_cm', '>=', (int) $minHeight);
        }

        if ($maxHeight = Arr::get($filters, 'max_height')) {
            $query->where('height_cm', '<=', (int) $maxHeight);
        }

        if ($minWeight = Arr::get($filters, 'min_weight')) {
            $query->where('weight_kg', '>=', (int) $minWeight);
        }

        if ($maxWeight = Arr::get($filters, 'max_weight')) {
            $query->where('weight_kg', '<=', (int) $maxWeight);
        }

        $minAge = Arr::get($filters, 'min_age');
        if ($minAge !== null && $minAge !== '') {
            $cutoff = Carbon::now()->subYears((int) $minAge)->endOfDay();
            $query->whereNotNull('dob')->whereDate('dob', '<=', $cutoff);
        }

        $maxAge = Arr::get($filters, 'max_age');
        if ($maxAge !== null && $maxAge !== '') {
            $cutoff = Carbon::now()->subYears(((int) $maxAge) + 1)->addDay()->startOfDay();
            $query->whereNotNull('dob')->whereDate('dob', '>=', $cutoff);
        }

        $badge = Arr::get($filters, 'badge');
        if ($badge === 'verified_identity') {
            $query->whereNotNull('verified_identity_at');
        } elseif ($badge === 'verified_academy') {
            $query->whereNotNull('verified_academy_at');
        }

        $hasVideo = Arr::get($filters, 'has_video');
        if ($hasVideo !== null && $hasVideo !== '') {
            $flag = filter_var($hasVideo, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            if ($flag === true) {
                $query->whereHas('media', fn ($mediaQuery) => $mediaQuery
                    ->where('type', 'video')
                    ->where('status', 'ready'));
            } elseif ($flag === false) {
                $query->whereDoesntHave('media', fn ($mediaQuery) => $mediaQuery
                    ->where('type', 'video')
                    ->where('status', 'ready'));
            }
        }

        if ($availability = Arr::get($filters, 'availability')) {
            $query->where('availability', $availability);
        }

        $lastActive = Arr::get($filters, 'last_active');
        if ($lastActive !== null && $lastActive !== '') {
            $days = (int) $lastActive;

            if ($days > 0) {
                $query->whereNotNull('last_active_at')
                    ->where('last_active_at', '>=', Carbon::now()->subDays($days));
            }
        }

        $keywords = trim((string) ($filters['keywords'] ?? $filters['q'] ?? ''));
        $hasKeywords = $keywords !== '';

        if ($hasKeywords) {
            $connection = $query->getModel()->getConnection();

            if ($connection->getDriverName() === 'pgsql') {
                $query->whereRaw("search_vector @@ plainto_tsquery('simple', ?)", [$keywords])
                    ->selectRaw("ts_rank_cd(search_vector, plainto_tsquery('simple', ?)) as relevance", [$keywords]);
                if ($sort === 'newest') {
                    $sort = 'relevance';
                }
            } else {
                $terms = collect(preg_split('/[\s،,\.]+/u', $keywords) ?: [])
                    ->map(fn ($term) => trim($term))
                    ->filter();

                if ($terms->isNotEmpty()) {
                    $query->where(function (Builder $builder) use ($terms) {
                        foreach ($terms as $term) {
                            $builder->where('searchable_text', 'like', '%'.$term.'%');
                        }
                    });
                }

                $query->selectRaw('0 as relevance');

                if ($sort === 'newest') {
                    $sort = 'relevance';
                }
            }
        } elseif ($sort === 'relevance') {
            $sort = 'newest';
        }

        return match ($sort) {
            'relevance' => $query->orderByDesc('relevance')->orderByDesc('view_count'),
            'recently_active' => $query->orderByDesc('last_active_at')->orderByDesc('updated_at'),
            'most_viewed' => $query->orderByDesc('view_count'),
            'top_rated' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('created_at'),
        };
    }
}
