@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('opportunities.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; @lang('Back to opportunities')</a>

        <article class="bg-white shadow rounded-lg p-6">
            <header class="border-b pb-4 mb-4">
                <h1 class="text-3xl font-bold text-slate-900">{{ $opportunity->title }}</h1>
                <p class="mt-2 text-slate-600">@lang('Posted by :club', ['club' => $opportunity->club->name])</p>
                <p class="mt-1 text-slate-500">{{ $opportunity->location_city }}, {{ $opportunity->location_country }}</p>
                <p class="mt-1 text-sm text-slate-500">@lang('Application deadline: :date', ['date' => optional($opportunity->deadline_at)->translatedFormat('d M Y')])</p>
            </header>

            <section class="prose max-w-none text-slate-800">
                {!! nl2br(e($opportunity->description)) !!}
            </section>

            <section class="mt-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-2">@lang('Requirements')</h2>
                @if (! empty($opportunity->requirements))
                    <ul class="list-disc space-y-2 pl-5 text-slate-700">
                        @foreach ($opportunity->requirements as $requirement)
                            <li><span class="font-semibold">{{ $requirement['label'] ?? '' }}:</span> {{ $requirement['value'] ?? '' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">@lang('No requirements provided.')</p>
                @endif
            </section>
        </article>

        <section class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-3">@lang('Apply now')</h2>
            @auth
                @if (auth()->user()->hasAnyRole(['player', 'agent']))
                    <form method="POST" action="{{ route('opportunities.apply', $opportunity) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="media_id">@lang('Attach highlight (optional)')</label>
                            <select id="media_id" name="media_id" class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring">
                                <option value="">@lang('No video attached')</option>
                                @foreach ($playerMedia as $media)
                                    <option value="{{ $media->id }}">{{ $media->original_filename ?? __('Untitled clip') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="note">@lang('Motivation letter (optional)')</label>
                            <textarea id="note" name="note" rows="4" class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">@lang('Submit application')</button>
                    </form>
                @else
                    <p class="text-sm text-slate-600">@lang('Sign in as a player or agent to apply.')</p>
                @endif
            @else
                <a href="{{ route('login') }}" class="inline-flex px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">@lang('Sign in to apply')</a>
            @endauth
        </section>
    </div>
@endsection
