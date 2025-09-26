@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">@lang('Messages')</h1>

@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

<div class="bg-white p-4 rounded shadow">
    <h2 class="text-lg font-semibold mb-3">@lang('Start a new conversation')</h2>
    <form method="POST" action="{{ route('dashboard.messages.start') }}" class="grid gap-3 md:grid-cols-2">
        @csrf
        <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="recipient_email">@lang('Recipient email')</label>
            <input type="email" name="recipient_email" id="recipient_email" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>
        <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="subject">@lang('Subject (optional)')</label>
            <input type="text" name="subject" id="subject" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="body">@lang('Message')</label>
            <textarea name="body" id="body" rows="3" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
        </div>
        <div class="col-span-2 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">@lang('Send')</button>
        </div>
    </form>
</div>

<div class="grid gap-4 mt-6">
    @forelse($threads as $thread)
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-lg font-semibold">{{ $thread->subject ?? __('Conversation') }}</h2>
                <div class="text-xs text-gray-500">
                    @lang('Participants'): {{ $thread->participants->pluck('user.name')->filter()->implode(', ') }}
                </div>
            </div>
            <div class="space-y-2 mt-3">
                @foreach($thread->messages->sortBy('created_at') as $message)
                    <div class="text-sm">
                        <strong>{{ $message->sender->name }}:</strong>
                        <span>{{ $message->body }}</span>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('dashboard.messages.store', $thread) }}" class="mt-4 grid gap-2">
                @csrf
                <textarea class="border rounded p-2" name="body" rows="2" placeholder="@lang('Reply')" required></textarea>
                <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">@lang('Send')</button>
            </form>
        </div>
    @empty
        <p class="text-sm text-gray-600">@lang('No conversations yet. Start one above!')</p>
    @endforelse
</div>

<div class="mt-4">
    {{ $threads->links() }}
</div>
@endsection
