@php($opportunity = $opportunity ?? null)
<div>
    <label class="block text-sm font-medium">@lang('Title')</label>
    <input class="mt-1 border rounded w-full p-2" name="title" value="{{ old('title', optional($opportunity)->title) }}">
</div>
<div>
    <label class="block text-sm font-medium">@lang('Description')</label>
    <textarea class="mt-1 border rounded w-full p-2" name="description" rows="4">{{ old('description', optional($opportunity)->description) }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium">@lang('Requirements (JSON)')</label>
    <textarea class="mt-1 border rounded w-full p-2" name="requirements_json" rows="4" placeholder='[{"label":"Age","value":"16-18"}]'>{{ old('requirements_json', optional($opportunity)->requirements ? json_encode($opportunity->requirements) : '') }}</textarea>
</div>
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">@lang('City')</label>
        <input class="mt-1 border rounded w-full p-2" name="location_city" value="{{ old('location_city', optional($opportunity)->location_city) }}">
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Country')</label>
        <input class="mt-1 border rounded w-full p-2" name="location_country" value="{{ old('location_country', optional($opportunity)->location_country) }}">
    </div>
</div>
<div>
    <label class="block text-sm font-medium">@lang('Deadline')</label>
    <input type="date" class="mt-1 border rounded w-full p-2" name="deadline_at" value="{{ old('deadline_at', optional(optional($opportunity)->deadline_at)->toDateString()) }}">
</div>
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">@lang('Status')</label>
        <select class="mt-1 border rounded w-full p-2" name="status">
            @foreach(['draft','published','archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', optional($opportunity)->status) === $status)>@lang(ucfirst($status))</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">@lang('Visibility')</label>
        <select class="mt-1 border rounded w-full p-2" name="visibility">
            @foreach(['public','private'] as $visibility)
                <option value="{{ $visibility }}" @selected(old('visibility', optional($opportunity)->visibility) === $visibility)>@lang(ucfirst($visibility))</option>
            @endforeach
        </select>
    </div>
</div>
