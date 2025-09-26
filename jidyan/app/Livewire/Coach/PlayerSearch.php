<?php

namespace App\Livewire\Coach;

use App\Models\Coach;
use App\Models\PlayerProfile;
use App\Queries\PlayerSearch as PlayerSearchQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class PlayerSearch extends Component
{
    use WithPagination;

    public array $filters = [
        'keywords' => '',
        'position' => '',
        'city' => '',
        'country' => '',
        'preferred_foot' => '',
        'min_height' => '',
        'max_height' => '',
        'min_weight' => '',
        'max_weight' => '',
        'min_age' => '',
        'max_age' => '',
        'has_video' => '',
        'badge' => '',
        'availability' => '',
        'last_active' => '',
    ];

    public string $sort = 'newest';
    public int $perPage = 12;
    public ?int $selectedShortlistId = null;
    public string $newShortlistTitle = '';
    public array $notes = [];

    protected $queryString = [
        'filters.keywords' => ['except' => ''],
        'filters.position' => ['except' => ''],
        'filters.city' => ['except' => ''],
        'filters.country' => ['except' => ''],
        'filters.preferred_foot' => ['except' => ''],
        'filters.min_height' => ['except' => ''],
        'filters.max_height' => ['except' => ''],
        'filters.min_weight' => ['except' => ''],
        'filters.max_weight' => ['except' => ''],
        'filters.min_age' => ['except' => ''],
        'filters.max_age' => ['except' => ''],
        'filters.has_video' => ['except' => ''],
        'filters.badge' => ['except' => ''],
        'filters.availability' => ['except' => ''],
        'filters.last_active' => ['except' => ''],
        'sort' => ['except' => 'newest'],
        'page' => ['except' => 1],
    ];

    public function mount(array $filters = [], string $sort = 'newest', ?int $shortlist = null): void
    {
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $this->filters) && $value !== null && $value !== '') {
                $this->filters[$key] = (string) $value;
            }
        }

        $this->sort = $sort;

        if (trim((string) $this->filters['keywords']) !== '' && $this->sort === 'newest') {
            $this->sort = 'relevance';
        }

        $this->selectedShortlistId = $shortlist ?? $this->defaultShortlistId();

        if ($this->selectedShortlistId) {
            $this->loadNotesForShortlist($this->selectedShortlistId);
        }
    }

    public function updatingFilters(): void
    {
        $this->resetPage();
    }

    public function updatedFiltersKeywords($value): void
    {
        $keywords = trim((string) $value);

        if ($keywords === '' && $this->sort === 'relevance') {
            $this->sort = 'newest';
        } elseif ($keywords !== '' && $this->sort === 'newest') {
            $this->sort = 'relevance';
        }
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('filters');
        $this->resetPage();
    }

    public function updatedSelectedShortlistId($value): void
    {
        $id = is_numeric($value) ? (int) $value : null;
        $this->selectedShortlistId = $id;

        if ($id) {
            $this->loadNotesForShortlist($id);
        } else {
            $this->notes = [];
        }
    }

    public function createShortlist(): void
    {
        $coach = $this->coach();

        if (! $coach) {
            $this->addError('selectedShortlistId', __('Coach profile required.'));

            return;
        }

        $this->validate([
            'newShortlistTitle' => ['required', 'string', 'max:120'],
        ]);

        $shortlist = $coach->shortlists()->create([
            'title' => $this->newShortlistTitle,
        ]);

        $this->newShortlistTitle = '';
        $this->selectedShortlistId = $shortlist->id;
        $this->loadNotesForShortlist($shortlist->id);
        $this->resetErrorBag('selectedShortlistId');

        $this->dispatch('shortlist-created');
    }

    public function addToShortlist(int $playerId): void
    {
        $coach = $this->coach();

        if (! $coach || ! $this->selectedShortlistId) {
            $this->addError('selectedShortlistId', __('Select a shortlist before adding players.'));

            return;
        }

        $shortlist = $coach->shortlists()->find($this->selectedShortlistId);

        if (! $shortlist) {
            $this->addError('selectedShortlistId', __('Shortlist not found.'));

            return;
        }

        $player = PlayerProfile::visible()->find($playerId);

        if (! $player) {
            $this->addError('selectedShortlistId', __('Player unavailable.'));

            return;
        }

        $note = Arr::get($this->notes, $playerId, null);

        Validator::make([
            'note' => $note,
        ], [
            'note' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $shortlist->items()->updateOrCreate(
            ['player_id' => $player->id],
            ['note' => $note]
        );

        $this->loadNotesForShortlist($shortlist->id);
        $this->resetErrorBag('selectedShortlistId');
        $this->dispatch('player-added', playerId: $player->id);
    }

    public function removeFromShortlist(int $playerId): void
    {
        $coach = $this->coach();

        if (! $coach || ! $this->selectedShortlistId) {
            return;
        }

        $shortlist = $coach->shortlists()->find($this->selectedShortlistId);

        if (! $shortlist) {
            return;
        }

        $shortlist->items()->where('player_id', $playerId)->delete();
        unset($this->notes[$playerId]);

        $this->dispatch('player-removed', playerId: $playerId);
    }

    public function render(): View
    {
        /** @var LengthAwarePaginator $players */
        $players = PlayerSearchQuery::build($this->cleanFilters(), $this->sort)
            ->paginate($this->perPage);

        $coach = $this->coach();
        $shortlists = $coach?->shortlists()->withCount('items')->get() ?? collect();

        $selectedShortlistPlayers = [];
        if ($coach && $this->selectedShortlistId) {
            $selectedShortlistPlayers = $coach->shortlists()
                ->with(['items' => fn ($query) => $query->select('id', 'shortlist_id', 'player_id')])
                ->find($this->selectedShortlistId)?->items
                ->pluck('player_id')
                ->all() ?? [];
        }

        return view('livewire.coach.player-search', [
            'players' => $players,
            'shortlists' => $shortlists,
            'selectedPlayerIds' => $selectedShortlistPlayers,
            'availabilityOptions' => PlayerProfile::AVAILABILITY_OPTIONS,
            'lastActiveOptions' => [7, 30, 90],
        ]);
    }

    protected function coach(): ?Coach
    {
        return Auth::user()?->coach;
    }

    protected function loadNotesForShortlist(int $shortlistId): void
    {
        $coach = $this->coach();

        if (! $coach) {
            $this->notes = [];

            return;
        }

        $shortlist = $coach->shortlists()->with('items')->find($shortlistId);

        if (! $shortlist) {
            $this->notes = [];
            $this->selectedShortlistId = null;

            return;
        }

        $this->notes = $shortlist->items
            ->mapWithKeys(fn ($item) => [$item->player_id => (string) ($item->note ?? '')])
            ->toArray();
    }

    protected function cleanFilters(): array
    {
        return array_filter($this->filters, fn ($value) => $value !== null && $value !== '');
    }

    protected function defaultShortlistId(): ?int
    {
        return $this->coach()?->shortlists()->oldest()->value('id');
    }
}
