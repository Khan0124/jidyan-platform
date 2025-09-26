@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">@lang('Opportunities')</h1>
    <a href="{{ route('dashboard.club.opportunities.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">@lang('New Opportunity')</a>
</div>

<div class="bg-white rounded shadow divide-y">
    @foreach($opportunities as $opportunity)
        <div class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold">{{ $opportunity->title }}</h2>
                <p class="text-sm text-slate-500">{{ $opportunity->location_city }}, {{ $opportunity->location_country }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    {{ trans_choice('messages.applications.count', $opportunity->applications_count, ['count' => $opportunity->applications_count]) }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.club.opportunities.show', $opportunity) }}" class="text-sm text-slate-600 underline decoration-dotted">
                    @lang('Manage pipeline')
                </a>
                <a href="{{ route('dashboard.club.opportunities.edit', $opportunity) }}" class="text-blue-600 text-sm">@lang('Edit')</a>
            </div>
        </div>
    @endforeach
</div>

{{ $opportunities->links() }}
@endsection
