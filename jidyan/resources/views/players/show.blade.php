@extends('layouts.app')

@section('content')
<div class="grid gap-6">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-3xl font-semibold">{{ $player->user->name }}</h1>
        <p class="text-slate-500">{{ $player->position }} • {{ $player->city }}, {{ $player->country }}</p>
        <div class="flex flex-wrap gap-2 mt-3">
            @if($player->verified_identity_at)
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">@lang('Verified identity')</span>
            @endif
            @if($player->verified_academy_at)
                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">@lang('Verified academy')</span>
            @endif
        </div>
        <div class="mt-4 grid gap-2 text-sm">
            <div>@lang('Height'): {{ $player->height_cm }} cm</div>
            <div>@lang('Weight'): {{ $player->weight_kg }} kg</div>
            <div>@lang('Preferred Foot'): {{ ucfirst($player->preferred_foot) }}</div>
            <div>@lang('Current Club'): {{ $player->current_club }}</div>
            <div>@lang('Availability'): {{ $player->availabilityLabel() }}</div>
            <div>@lang('Last active'): {{ $player->last_active_at ? $player->last_active_at->diffForHumans() : __('No recent activity') }}</div>
        </div>
    </div>

    <div class="bg-white shadow rounded p-6">
        <h2 class="text-2xl font-semibold mb-4">@lang('Media')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($player->media as $media)
                @if($media->type === 'video' && $media->isReady())
                    <div class="grid gap-2">
                        <div class="aspect-video bg-black">
                            <video
                                controls
                                preload="metadata"
                                poster="{{ asset($media->poster_path) }}"
                                data-sign-endpoint="{{ route('media.signed-url', $media) }}"
                                data-error-message="{{ __('Unable to load video stream.') }}"
                                class="w-full h-full rounded"
                            >
                                <p>@lang('Your browser does not support HTML5 video.')</p>
                            </video>
                        </div>
                        <div class="text-sm text-slate-500 flex items-center justify-between">
                            <span>{{ $media->original_filename ?? __('Untitled clip') }}</span>
                            <span>{{ $media->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="bg-white shadow rounded p-6">
        <h2 class="text-2xl font-semibold mb-4">@lang('Statistics')</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left">
                    <th class="py-2">@lang('Season')</th>
                    <th>@lang('Matches')</th>
                    <th>@lang('Goals')</th>
                    <th>@lang('Assists')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($player->stats as $stat)
                <tr class="border-t">
                    <td class="py-2">{{ $stat->season }}</td>
                    <td>{{ $stat->matches }}</td>
                    <td>{{ $stat->goals }}</td>
                    <td>{{ $stat->assists }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loadStream = (video, url) => {
            if (window.Hls && Hls.isSupported()) {
                const hls = new Hls({
                    maxBufferLength: 30,
                    maxMaxBufferLength: 60,
                });
                hls.loadSource(url);
                hls.attachMedia(video);

                return;
            }

            video.src = url;
            video.load();
        };

        const attachError = (video) => {
            const message = video.dataset.errorMessage;

            if (!message || video.dataset.errorShown) {
                return;
            }

            const hint = document.createElement('p');
            hint.className = 'text-sm text-red-500';
            hint.textContent = message;
            video.insertAdjacentElement('afterend', hint);
            video.dataset.errorShown = 'true';
        };

        document.querySelectorAll('video[data-sign-endpoint]').forEach((video) => {
            const endpoint = video.dataset.signEndpoint;

            if (!endpoint) {
                return;
            }

            fetch(endpoint, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to sign URL');
                    }

                    return response.json();
                })
                .then((payload) => {
                    if (!payload?.url) {
                        throw new Error('Missing URL');
                    }

                    loadStream(video, payload.url);
                })
                .catch(() => {
                    attachError(video);
                });
        });
    });
</script>
@endpush
