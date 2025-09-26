@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">@lang('Content Reports')</h1>

<form method="GET" class="mb-4 flex items-center gap-3">
    <label class="text-sm font-medium" for="status">@lang('Filter by status')</label>
    <select id="status" name="status" class="border rounded p-2 text-sm">
        <option value="">@lang('All')</option>
        <option value="pending" @selected(request('status') === 'pending')>@lang('Pending')</option>
        <option value="resolved" @selected(request('status') === 'resolved')>@lang('Resolved')</option>
        <option value="dismissed" @selected(request('status') === 'dismissed')>@lang('Dismissed')</option>
    </select>
    <button class="bg-slate-800 text-white px-3 py-2 rounded text-sm">@lang('Apply')</button>
</form>

<div class="bg-white rounded shadow divide-y">
    @forelse($reports as $report)
        <div class="p-4 space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-semibold">{{ $report->reason }}</h2>
                    <p class="text-sm text-slate-500">
                        @lang('Reported by :name', ['name' => $report->reporter->name]) • {{ $report->created_at->diffForHumans() }}
                    </p>
                    <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">
                        {{ \Illuminate\Support\Str::title(class_basename($report->reportable_type)) }}
                    </p>
                </div>
                <span class="px-2 py-1 rounded text-xs font-semibold"
                    @class([
                        'bg-yellow-100 text-yellow-800' => $report->status === 'pending',
                        'bg-green-100 text-green-800' => $report->status === 'resolved',
                        'bg-slate-200 text-slate-700' => $report->status === 'dismissed',
                    ])
                >
                    {{ \Illuminate\Support\Str::title($report->status) }}
                </span>
            </div>

            @if($report->description)
                <p class="text-sm text-slate-700">{{ $report->description }}</p>
            @endif

            <div class="text-sm text-slate-600">
                @php
                    $reportable = $report->reportable;
                @endphp
                @if($reportable instanceof \App\Models\PlayerProfile)
                    <a href="{{ route('players.show', $reportable) }}" class="text-blue-600">
                        @lang('View player profile: :name', ['name' => $reportable->user->name])
                    </a>
                @elseif($reportable instanceof \App\Models\PlayerMedia)
                    <span>@lang('Reported media'): {{ $reportable->original_filename ?? $reportable->id }}</span>
                @elseif($reportable instanceof \App\Models\Opportunity)
                    <span>@lang('Opportunity'): {{ $reportable->title }}</span>
                @else
                    <span class="text-slate-500">@lang('The content has been removed.')</span>
                @endif
            </div>

            <form method="POST" action="{{ route('reports.update', $report) }}" class="flex flex-wrap gap-2 items-center">
                @csrf
                @method('PATCH')
                <select name="status" class="border rounded p-2 text-sm">
                    <option value="pending" @selected($report->status === 'pending')>@lang('Pending')</option>
                    <option value="resolved" @selected($report->status === 'resolved')>@lang('Resolved')</option>
                    <option value="dismissed" @selected($report->status === 'dismissed')>@lang('Dismissed')</option>
                </select>
                <input type="text" name="resolution_notes" value="{{ old('resolution_notes', $report->resolution_notes) }}" placeholder="@lang('Notes')" class="border rounded p-2 text-sm flex-1" />
                <button class="bg-blue-600 text-white px-3 py-2 rounded text-sm">@lang('Update')</button>
            </form>

            @if($report->resolver)
                <p class="text-xs text-slate-500">
                    @lang('Last updated by :name :time', ['name' => $report->resolver->name, 'time' => $report->resolved_at?->diffForHumans() ?? ''])
                </p>
            @endif
        </div>
    @empty
        <p class="p-6 text-center text-slate-500">@lang('No reports found.')</p>
    @endforelse
</div>

<div class="mt-4">
    {{ $reports->links() }}
</div>
@endsection
