<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-5">
        @foreach($stages as $stage)
            <div class="rounded-lg bg-slate-100 p-4" wire:key="column-{{ $stage }}">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-600">
                    {{ __("applications.status.$stage") }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ trans_choice('messages.applications.count', $columns[$stage]->count(), ['count' => $columns[$stage]->count()]) }}
                </p>
                <div class="mt-4 space-y-3">
                    @forelse($columns[$stage] as $application)
                        <div class="rounded border border-slate-200 bg-white p-3 shadow-sm" wire:key="application-{{ $application->id }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold">{{ $application->player->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->player->position }} · {{ $application->player->country }}</p>
                                </div>
                                <span class="text-[10px] uppercase text-slate-400">{{ $application->updated_at->diffForHumans() }}</span>
                            </div>
                            @if($application->note)
                                <p class="mt-2 rounded bg-slate-50 p-2 text-xs text-slate-600">{{ $application->note }}</p>
                            @endif
                            <label class="mt-3 block text-xs font-semibold text-slate-600" for="pipeline-stage-{{ $application->id }}">
                                @lang('applications.move_to_stage')
                            </label>
                            <select
                                id="pipeline-stage-{{ $application->id }}"
                                class="mt-1 w-full rounded border border-slate-300 px-2 py-1 text-sm"
                                wire:change="moveTo({{ $application->id }}, $event.target.value)"
                            >
                                @foreach($stages as $option)
                                    <option value="{{ $option }}" @selected($option === $stage)>{{ __("applications.status.$option") }}</option>
                                @endforeach
                            </select>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">@lang('applications.stage_empty')</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
