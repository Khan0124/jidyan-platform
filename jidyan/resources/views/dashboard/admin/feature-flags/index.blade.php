@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('messages.Feature Flags') }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('messages.Manage runtime features safely by toggling them on or off.') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded bg-green-100 border border-green-200 text-green-900 px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 shadow rounded divide-y divide-gray-200 dark:divide-gray-800">
        @forelse ($flags as $flag)
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::headline($flag->key) }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $flag->description ?? __('messages.No description provided.') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('feature-flags.update', $flag) }}" class="text-right space-y-2">
                        @csrf
                        @method('PUT')
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1" @checked($flag->enabled) class="sr-only peer">
                            <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer peer-checked:bg-primary-500 relative transition">
                                <span class="absolute top-0.5 left-0.5 h-5 w-5 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition"></span>
                            </span>
                        </label>
                        <input type="hidden" name="description" value="{{ $flag->description }}">
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded">
                            {{ __('messages.Save changes') }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-6 text-gray-600 dark:text-gray-300">
                {{ __('messages.No feature flags configured yet.') }}
            </div>
        @endforelse
    </div>
</div>
@endsection
