@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">@lang('Applications')</h1>
<div class="bg-white rounded shadow divide-y">
    @foreach($applications as $application)
        <div class="p-4 grid gap-2">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">{{ $application->player->user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $application->opportunity->title }}</p>
                </div>
                <span class="text-sm uppercase tracking-wide">{{ $application->status }}</span>
            </div>
            <p class="text-sm">{{ $application->note }}</p>
        </div>
    @endforeach
</div>
{{ $applications->links() }}
@endsection
