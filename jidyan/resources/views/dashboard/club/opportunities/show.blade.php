@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-semibold">{{ $opportunity->title }}</h1>
        <p class="text-slate-500 text-sm">
            {{ $opportunity->location_city }}, {{ $opportunity->location_country }} ·
            @lang('Deadline'): {{ $opportunity->deadline_at->translatedFormat('d M Y') }}
        </p>
    </div>
    <a href="{{ route('dashboard.club.opportunities.edit', $opportunity) }}"
       class="inline-flex items-center justify-center bg-blue-600 text-white px-4 py-2 rounded shadow-sm text-sm">
        @lang('Edit opportunity')
    </a>
</div>

@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ session('status') }}
    </div>
@endif

<livewire:club.opportunity-pipeline :opportunity="$opportunity" />
@endsection
