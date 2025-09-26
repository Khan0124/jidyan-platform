@extends('layouts.app')

@section('content')
<div class="grid gap-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">@lang('Player Search')</h1>
            <p class="text-sm text-slate-500">@lang('Filter public player profiles, review clips, and build private shortlists.')</p>
        </div>
    </header>

    <livewire:coach.player-search :filters="$filters" :sort="$sort" />
</div>
@endsection
