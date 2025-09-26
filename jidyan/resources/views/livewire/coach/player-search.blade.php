@php use Illuminate\Support\Str; @endphp
<div class="grid gap-6">
    <section class="bg-white shadow rounded p-6 grid gap-4">
        <h2 class="text-xl font-semibold">@lang('Filters')</h2>
        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="md:col-span-2 xl:col-span-4">
                <label class="block text-sm font-medium">@lang('Keywords')</label>
                <input type="search" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.keywords" placeholder="@lang('Search player names, clubs, positions...')">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Position')</label>
                <input type="text" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.position" placeholder="@lang('e.g. RW')">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('City')</label>
                <input type="text" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.city">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Country')</label>
                <input type="text" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.country">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Preferred foot')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="filters.preferred_foot">
                    <option value="">@lang('Any')</option>
                    <option value="left">@lang('Left')</option>
                    <option value="right">@lang('Right')</option>
                    <option value="both">@lang('Both')</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Min height (cm)')</label>
                <input type="number" min="120" max="230" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.min_height">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Max height (cm)')</label>
                <input type="number" min="120" max="230" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.max_height">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Min weight (kg)')</label>
                <input type="number" min="40" max="130" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.min_weight">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Max weight (kg)')</label>
                <input type="number" min="40" max="130" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.max_weight">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Min age')</label>
                <input type="number" min="10" max="60" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.min_age">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Max age')</label>
                <input type="number" min="10" max="60" class="mt-1 border rounded w-full p-2" wire:model.debounce.500ms="filters.max_age">
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Badge')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="filters.badge">
                    <option value="">@lang('Any badge')</option>
                    <option value="verified_identity">@lang('Identity verified')</option>
                    <option value="verified_academy">@lang('Academy verified')</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Has video')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="filters.has_video">
                    <option value="">@lang('Any')</option>
                    <option value="1">@lang('Ready video available')</option>
                    <option value="0">@lang('No ready video')</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Availability')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="filters.availability">
                    <option value="">@lang('Any availability')</option>
                    @foreach($availabilityOptions as $option)
                        @php
                            $label = match($option) {
                                'available' => __('Available now'),
                                'contracted' => __('Under contract'),
                                'injured' => __('Injured / rehab'),
                                default => __('Availability unknown'),
                            };
                        @endphp
                        <option value="{{ $option }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">@lang('Last active')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="filters.last_active">
                    <option value="">@lang('Any time')</option>
                    @foreach($lastActiveOptions as $days)
                        <option value="{{ $days }}">@lang('Within :days days', ['days' => $days])</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">@lang('Sort')</label>
                <select class="mt-1 border rounded w-full p-2" wire:model="sort">
                    <option value="relevance">@lang('Best match')</option>
                    <option value="newest">@lang('Newest')</option>
                    <option value="most_viewed">@lang('Most viewed')</option>
                    <option value="top_rated">@lang('Top rated')</option>
                    <option value="recently_active">@lang('Recently active')</option>
                </select>
            </div>
            <div class="flex items-end justify-end">
                <button type="button" class="text-sm text-slate-500" wire:click="resetFilters">@lang('Clear filters')</button>
            </div>
        </div>
    </section>

    <section class="bg-white shadow rounded p-6 grid gap-4">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">@lang('Shortlists')</h2>
                <p class="text-sm text-slate-500">@lang('Organise promising players into private lists visible only to your coaching staff.')</p>
            </div>
            <div class="grid gap-2 md:grid-cols-[minmax(0,240px)_auto] md:items-center">
                <select class="border rounded p-2" wire:model="selectedShortlistId">
                    <option value="">@lang('Select shortlist')</option>
                    @foreach($shortlists as $shortlist)
                        <option value="{{ $shortlist->id }}">{{ $shortlist->title }} ({{ $shortlist->items_count }})</option>
                    @endforeach
                </select>
                <div class="text-sm text-slate-500 md:text-right">
                    @lang('Manage players below to add notes or remove entries.')
                </div>
            </div>
        </header>
        <div class="grid md:grid-cols-[minmax(0,320px)_auto_auto] gap-4 md:items-end">
            <div>
                <label class="block text-sm font-medium">@lang('New shortlist title')</label>
                <input type="text" class="mt-1 border rounded w-full p-2" wire:model.defer="newShortlistTitle" placeholder="@lang('Example: U18 targets')">
            </div>
            <div class="md:justify-self-end">
                <button type="button" wire:click="createShortlist" class="bg-blue-600 text-white px-4 py-2 rounded">@lang('Create shortlist')</button>
            </div>
        </div>
        @error('selectedShortlistId')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('newShortlistTitle')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </section>

    <section class="grid gap-4">
        @forelse($players as $player)
            <article class="bg-white shadow rounded p-6 grid gap-4">
                <header class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="grid gap-1">
                        <h3 class="text-2xl font-semibold">{{ $player->user->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $player->position }} • {{ $player->city }}, {{ $player->country }}</p>
                        <div class="flex flex-wrap gap-2 text-xs uppercase tracking-wide text-slate-500">
                            @if($player->preferred_foot)
                                <span class="px-2 py-1 bg-slate-100 rounded">@lang('Foot'): {{ ucfirst($player->preferred_foot) }}</span>
                            @endif
                            @if($player->hasBadge('verified_identity'))
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded">@lang('Identity verified')</span>
                            @endif
                            @if($player->hasBadge('verified_academy'))
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">@lang('Academy verified')</span>
                            @endif
                            @if($player->media->count())
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded">{{ trans_choice('{0}No clips|{1}1 clip|[2,*]:count clips', $player->media->count(), ['count' => $player->media->count()]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-sm text-slate-500 md:text-right grid gap-1">
                        @if($player->dob)
                            <span>@lang('Age'): {{ $player->dob->age }}</span>
                        @endif
                        <span>@lang('Availability'): {{ $player->availabilityLabel() }}</span>
                        @if($player->height_cm)
                            <span>@lang('Height'): {{ $player->height_cm }} cm</span>
                        @endif
                        @if($player->weight_kg)
                            <span>@lang('Weight'): {{ $player->weight_kg }} kg</span>
                        @endif
                        <span>@lang('Last active'): {{ $player->last_active_at ? $player->last_active_at->diffForHumans() : __('No recent activity') }}</span>
                        <span>@lang('Views'): {{ number_format($player->view_count) }}</span>
                    </div>
                </header>
                <div class="grid md:grid-cols-[minmax(0,1fr)_320px] gap-4">
                    <div class="prose max-w-none text-sm text-slate-700">
                        {!! nl2br(e(Str::limit($player->bio, 400))) !!}
                    </div>
                    <div class="grid gap-3">
                        <label class="block text-sm font-medium">@lang('Private note')</label>
                        <textarea class="border rounded w-full p-2 text-sm" rows="3" wire:model.defer="notes.{{ $player->id }}" placeholder="@lang('Your observation for coaching staff only')"></textarea>
                        <div class="flex items-center gap-3">
                            @if(in_array($player->id, $selectedPlayerIds))
                                <button type="button" wire:click="removeFromShortlist({{ $player->id }})" class="text-sm text-red-600">@lang('Remove from shortlist')</button>
                                <button type="button" wire:click="addToShortlist({{ $player->id }})" class="text-sm text-blue-600">@lang('Update note')</button>
                            @else
                                <button type="button" wire:click="addToShortlist({{ $player->id }})" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">@lang('Add to shortlist')</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if($player->media->first())
                    <div class="grid gap-2">
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">@lang('Latest clip')</h4>
                        <div class="aspect-video bg-black rounded overflow-hidden">
                            <video controls preload="metadata" poster="{{ asset($player->media->first()->poster_path) }}" class="w-full h-full">
                                <source src="{{ asset($player->media->first()->hls_path) }}" type="application/x-mpegURL">
                                @lang('Your browser does not support the video tag.')
                            </video>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <div class="bg-white shadow rounded p-6 text-sm text-slate-500">@lang('No players match your filters yet. Try broadening the criteria.')</div>
        @endforelse
        <div>
            {{ $players->links() }}
        </div>
    </section>
</div>
