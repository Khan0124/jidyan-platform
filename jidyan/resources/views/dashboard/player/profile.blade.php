@php use App\Models\PlayerProfile; @endphp
@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('dashboard.player.profile.update') }}" class="bg-white p-6 rounded shadow grid gap-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium">@lang('City')</label>
        <input class="mt-1 border rounded w-full p-2" name="city" value="{{ old('city', $player->city) }}">
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Country')</label>
        <input class="mt-1 border rounded w-full p-2" name="country" value="{{ old('country', $player->country) }}">
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Position')</label>
        <input class="mt-1 border rounded w-full p-2" name="position" value="{{ old('position', $player->position) }}">
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Preferred Foot')</label>
        <select class="mt-1 border rounded w-full p-2" name="preferred_foot">
            <option value="left" @selected(old('preferred_foot', $player->preferred_foot) === 'left')>@lang('Left')</option>
            <option value="right" @selected(old('preferred_foot', $player->preferred_foot) === 'right')>@lang('Right')</option>
            <option value="both" @selected(old('preferred_foot', $player->preferred_foot) === 'both')>@lang('Both')</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Visibility')</label>
        <select class="mt-1 border rounded w-full p-2" name="visibility">
            <option value="public" @selected(old('visibility', $player->visibility) === 'public')>@lang('Public')</option>
            <option value="private" @selected(old('visibility', $player->visibility) === 'private')>@lang('Private')</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Availability')</label>
        <select class="mt-1 border rounded w-full p-2" name="availability">
            @foreach(PlayerProfile::AVAILABILITY_OPTIONS as $option)
                @php
                    $label = match($option) {
                        'available' => __('Available now'),
                        'contracted' => __('Under contract'),
                        'injured' => __('Injured / rehab'),
                        default => __('Availability unknown'),
                    };
                @endphp
                <option value="{{ $option }}" @selected(old('availability', $player->availability) === $option)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">@lang('Save')</button>
</form>

<section class="mt-8 bg-white p-6 rounded shadow grid gap-3">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">@lang('Verification badges')</h2>
            <p class="text-sm text-slate-500">@lang('Verified badges help clubs trust your profile.')</p>
        </div>
        <a href="{{ route('verifications.create') }}" class="text-sm text-blue-600 hover:underline">@lang('Manage verification')</a>
    </header>
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
</section>

<section class="mt-8 bg-white p-6 rounded shadow grid gap-4">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">@lang('Video Library')</h2>
            <p class="text-sm text-slate-500">@lang('Upload up to five clips (60 seconds, 120MB each). Interrupted uploads resume automatically.')</p>
        </div>
        <span class="text-sm text-slate-500">{{ $player->media->where('type', 'video')->count() }} / 5</span>
    </header>

    <form
        data-chunk-upload
        data-endpoint="{{ route('media.store') }}"
        data-max-size="125829120"
        data-limit="5"
        data-existing-count="{{ $player->media->where('type', 'video')->count() }}"
        data-ready-template="@lang('Ready to upload: :name')"
        data-uploading-template="@lang('Uploading :name (:percent%)')"
        data-complete-template="@lang('Upload complete: :name')"
        data-error-template="@lang('Upload failed: :name')"
        data-limit-message="@lang('Maximum 5 clips allowed.')"
        data-size-template="@lang('Video exceeds size limit of :limit MB.')"
        class="grid gap-4 bg-slate-50 border border-dashed border-slate-300 rounded p-4"
    >
        @csrf
        <div class="grid gap-2">
            <label class="text-sm font-medium" for="player-video-upload">@lang('Select up to :count clips (max :size MB each).', ['count' => 5, 'size' => 120])</label>
            <input
                id="player-video-upload"
                name="videos[]"
                type="file"
                accept="video/*"
                multiple
                class="rounded border border-slate-300 bg-white p-2"
                data-chunk-input
            >
        </div>
        <div class="flex items-center justify-between gap-4 text-sm">
            <p class="text-slate-500" data-upload-hint>@lang('Uploads will continue if your connection drops; keep this tab open until complete.')</p>
            <button type="submit" class="shrink-0 bg-blue-600 text-white px-4 py-2 rounded">@lang('Upload selected videos')</button>
        </div>
        <p class="text-sm text-red-600 hidden" data-upload-error></p>
    </form>

    <div class="space-y-2" data-upload-status aria-live="polite"></div>

    <div class="border rounded divide-y">
        @forelse($player->media as $media)
            <div class="p-4 grid gap-2 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <p class="font-medium">{{ $media->original_filename ?? __('Untitled clip') }}</p>
                    <p class="text-sm text-slate-500">{{ $media->created_at?->diffForHumans() }}</p>
                    <p class="text-sm">@lang('Status'): <span class="font-semibold">{{ ucfirst($media->status) }}</span></p>
                </div>
                @if($media->isReady())
                    <a href="{{ asset($media->hls_path) }}" class="text-sm text-blue-600" target="_blank">@lang('Preview')</a>
                @endif
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">@lang('No videos uploaded yet.')</p>
        @endforelse
    </div>
</section>

<section class="mt-8 bg-white p-6 rounded shadow grid gap-4">
    <header>
        <h2 class="text-xl font-semibold">@lang('Season statistics')</h2>
        <p class="text-sm text-slate-500">@lang('Keep your latest performance data up to date for coaches and clubs.')</p>
    </header>

    <form method="POST" action="{{ route('dashboard.player.stats.store') }}" class="grid gap-4 md:grid-cols-5 md:items-end">
        @csrf
        <div>
            <label class="block text-sm font-medium">@lang('Season')</label>
            <input name="season" class="mt-1 border rounded w-full p-2" placeholder="2023/24" required>
        </div>
        <div>
            <label class="block text-sm font-medium">@lang('Matches')</label>
            <input type="number" min="0" name="matches" class="mt-1 border rounded w-full p-2" value="0" required>
        </div>
        <div>
            <label class="block text-sm font-medium">@lang('Goals')</label>
            <input type="number" min="0" name="goals" class="mt-1 border rounded w-full p-2" value="0" required>
        </div>
        <div>
            <label class="block text-sm font-medium">@lang('Assists')</label>
            <input type="number" min="0" name="assists" class="mt-1 border rounded w-full p-2" value="0" required>
        </div>
        <div class="md:col-span-5">
            <label class="block text-sm font-medium">@lang('Notes')</label>
            <textarea name="notes" rows="2" class="mt-1 border rounded w-full p-2" placeholder="@lang('Optional context (competition, highlights, etc.)')"></textarea>
        </div>
        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">@lang('Add stat')</button>
        </div>
    </form>

    <div class="border rounded divide-y">
        @forelse($player->stats as $stat)
            <div class="p-4 grid gap-3">
                <form method="POST" action="{{ route('dashboard.player.stats.update', $stat) }}" class="grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 md:grid-cols-5 md:items-end">
                        <div>
                            <label class="block text-sm font-medium">@lang('Season')</label>
                            <input name="season" class="mt-1 border rounded w-full p-2" value="{{ $stat->season }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">@lang('Matches')</label>
                            <input type="number" min="0" name="matches" class="mt-1 border rounded w-full p-2" value="{{ $stat->matches }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">@lang('Goals')</label>
                            <input type="number" min="0" name="goals" class="mt-1 border rounded w-full p-2" value="{{ $stat->goals }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">@lang('Assists')</label>
                            <input type="number" min="0" name="assists" class="mt-1 border rounded w-full p-2" value="{{ $stat->assists }}" required>
                        </div>
                        <div class="md:col-span-5">
                            <label class="block text-sm font-medium">@lang('Notes')</label>
                            <textarea name="notes" rows="2" class="mt-1 border rounded w-full p-2">{{ $stat->notes }}</textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">@lang('Save changes')</button>
                        <a href="{{ route('players.show', $player) }}" class="text-sm text-slate-500">@lang('Preview profile')</a>
                    </div>
                </form>
                <form method="POST" action="{{ route('dashboard.player.stats.destroy', $stat) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600">@lang('Delete')</button>
                </form>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">@lang('No statistics added yet.')</p>
        @endforelse
    </div>
</section>
@endsection
