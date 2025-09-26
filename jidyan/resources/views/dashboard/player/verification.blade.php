@extends('layouts.app')

@section('content')
<div class="grid gap-6">
    <section class="bg-white p-6 rounded shadow grid gap-3">
        <h1 class="text-2xl font-semibold">@lang('Account verification')</h1>
        <p class="text-sm text-slate-500">
            @lang('Submit official documents to unlock verified badges on your profile.')
        </p>
        @if($player)
            <div class="grid gap-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="font-medium">@lang('Identity badge')</span>
                    @if($player->verified_identity_at)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">@lang('Active')</span>
                        <span class="text-slate-500">{{ $player->verified_identity_at->diffForHumans() }}</span>
                    @else
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">@lang('Not verified')</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-medium">@lang('Academy badge')</span>
                    @if($player->verified_academy_at)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">@lang('Active')</span>
                        <span class="text-slate-500">{{ $player->verified_academy_at->diffForHumans() }}</span>
                    @else
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">@lang('Not verified')</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <section class="bg-white p-6 rounded shadow grid gap-4">
        <h2 class="text-xl font-semibold">@lang('Start a new verification request')</h2>
        <form method="POST" action="{{ route('verifications.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
            @csrf
            <div class="grid gap-2">
                <label for="type" class="text-sm font-medium">@lang('Verification type')</label>
                <select id="type" name="type" class="border rounded p-2 text-sm" required>
                    <option value="identity">@lang('verification.type.identity')</option>
                    <option value="academy">@lang('verification.type.academy')</option>
                </select>
            </div>
            <div class="grid gap-2 md:col-span-2">
                <label for="document" class="text-sm font-medium">@lang('Upload document (PDF or image)')</label>
                <input id="document" name="document" type="file" accept="application/pdf,image/*" class="border rounded p-2 text-sm" required>
                <p class="text-xs text-slate-500">@lang('Maximum size: 10 MB. Please include clear scans of IDs or letters.')</p>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">@lang('Submit verification')</button>
            </div>
        </form>
    </section>

    <section class="bg-white p-6 rounded shadow grid gap-3">
        <h2 class="text-xl font-semibold">@lang('Recent submissions')</h2>
        <div class="border rounded divide-y">
            @forelse($verifications as $verification)
                <div class="p-4 grid gap-1 text-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ __('verification.type.'.$verification->type) }}</span>
                        <span class="px-2 py-1 rounded text-xs {{ $verification->status === 'approved' ? 'bg-green-100 text-green-700' : ($verification->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ __('verification.status.'.$verification->status) }}
                        </span>
                        <span class="text-slate-500">{{ $verification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-500">@lang('File'): {{ $verification->document_name ?? basename($verification->document_path) }}</p>
                    @if($verification->reason)
                        <p class="text-xs text-red-600">@lang('Reviewer note: :reason', ['reason' => $verification->reason])</p>
                    @endif
                </div>
            @empty
                <p class="p-4 text-sm text-slate-500">@lang('No verification requests yet.')</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
