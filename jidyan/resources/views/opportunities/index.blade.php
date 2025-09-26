@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="max-w-6xl mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900">@lang('Open opportunities')</h1>
            <p class="mt-2 text-slate-600">@lang('Search open opportunities across clubs and academies.')</p>
        </header>

        <section class="bg-white shadow rounded-lg p-6 mb-8">
            <form method="GET" class="grid md:grid-cols-4 gap-4 text-sm">
                <div class="md:col-span-2">
                    <label class="block font-medium text-slate-700" for="search">@lang('Search')</label>
                    <input id="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="@lang('Keywords, club or city')"
                        class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
                </div>
                <div>
                    <label class="block font-medium text-slate-700" for="country">@lang('Country')</label>
                    <input id="country" name="country" value="{{ $filters['country'] ?? '' }}"
                        class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
                </div>
                <div>
                    <label class="block font-medium text-slate-700" for="city">@lang('City')</label>
                    <input id="city" name="city" value="{{ $filters['city'] ?? '' }}"
                        class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
                </div>
                <div>
                    <label class="block font-medium text-slate-700" for="deadline_from">@lang('Deadline from')</label>
                    <input type="date" id="deadline_from" name="deadline_from" value="{{ $filters['deadline_from'] ?? '' }}"
                        class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
                </div>
                <div>
                    <label class="block font-medium text-slate-700" for="deadline_to">@lang('Deadline to')</label>
                    <input type="date" id="deadline_to" name="deadline_to" value="{{ $filters['deadline_to'] ?? '' }}"
                        class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
                </div>
                <div class="md:col-span-4 flex items-end gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">@lang('Apply filters')</button>
                    <a href="{{ route('opportunities.index') }}" class="px-4 py-2 border rounded text-slate-600 hover:text-slate-900">@lang('Reset')</a>
                </div>
            </form>
        </section>

        @if ($opportunities->isEmpty())
            <div class="bg-white shadow rounded-lg p-6 text-center text-slate-600">
                @lang('No opportunities available right now.')
            </div>
        @else
            <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($opportunities as $opportunity)
                    <article class="bg-white shadow rounded-lg p-5 flex flex-col justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $opportunity->title }}</h2>
                            <p class="mt-1 text-slate-600">@lang('Posted by :club', ['club' => $opportunity->club->name])</p>
                            <p class="mt-2 text-slate-500">{{ $opportunity->location_city }}, {{ $opportunity->location_country }}</p>
                            <p class="mt-2 text-sm text-slate-500">@lang('Application deadline: :date', ['date' => optional($opportunity->deadline_at)->translatedFormat('d M Y')])</p>
                            <p class="mt-3 text-sm text-slate-600 overflow-hidden" style="max-height: 5.5rem;">
                                {{ Str::limit($opportunity->description, 180) }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('opportunities.show', $opportunity) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">@lang('View opportunity')</a>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="mt-8">
                {{ $opportunities->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
