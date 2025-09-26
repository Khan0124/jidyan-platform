@extends('layouts.app')

@section('content')
<div class="grid gap-12">
    <section>
        <h1 class="text-3xl font-bold mb-4">@lang('Discover Football Talent')</h1>
        <p class="text-slate-600">@lang('Browse featured players and open opportunities across the region.')</p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4">@lang('Featured Players')</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredPlayers as $player)
                <div class="bg-white rounded shadow p-4">
                    <h3 class="text-xl font-semibold">{{ $player->user->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $player->position }} • {{ $player->city }}, {{ $player->country }}</p>
                    <a class="text-blue-600 text-sm" href="{{ route('players.show', $player) }}">@lang('View Profile')</a>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4">@lang('Opportunities')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($opportunities as $opportunity)
                <div class="bg-white rounded shadow p-4">
                    <h3 class="text-xl font-semibold">{{ $opportunity->title }}</h3>
                    <p class="text-sm text-slate-500">{{ $opportunity->location_city }}, {{ $opportunity->location_country }}</p>
                    <p class="text-sm">{{ \Illuminate\Support\Str::limit($opportunity->description, 120) }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
