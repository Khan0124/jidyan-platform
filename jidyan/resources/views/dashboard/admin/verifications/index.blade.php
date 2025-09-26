@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">@lang('Verification Queue')</h1>
<div class="bg-white rounded shadow divide-y">
    @foreach($verifications as $verification)
        <div class="p-4 grid gap-4">
            <div class="flex justify-between items-start gap-4">
                <div class="grid gap-1">
                    <h2 class="text-lg font-semibold">{{ $verification->user->name }}</h2>
                    <p class="text-sm text-slate-500">
                        {{ __('verification.type.'.$verification->type) }} • {{ ucfirst($verification->status) }}
                    </p>
                    <p class="text-xs text-slate-400">
                        @lang('Submitted :time', ['time' => $verification->created_at->diffForHumans()])
                    </p>
                    <a
                        href="{{ route('verifications.download', $verification) }}"
                        class="text-sm text-blue-600 hover:underline"
                    >
                        @lang('Download document (:name)', ['name' => $verification->document_name ?? __('Verification file')])
                    </a>
                </div>
                <form method="POST" action="{{ route('verifications.update', $verification) }}" class="grid gap-2 text-sm w-full max-w-sm">
                    @csrf
                    @method('PUT')
                    <label class="grid gap-1">
                        <span class="font-medium">@lang('Decision')</span>
                        <select name="status" class="border rounded p-2">
                            <option value="approved">@lang('Approve')</option>
                            <option value="rejected">@lang('Reject')</option>
                        </select>
                    </label>
                    <label class="grid gap-1">
                        <span class="font-medium">@lang('Reviewer note (optional)')</span>
                        <textarea name="reason" rows="2" class="border rounded p-2" placeholder="@lang('Reason for this decision')">{{ old('reason', $verification->reason) }}</textarea>
                    </label>
                    <button class="bg-blue-600 text-white px-3 py-2 rounded">@lang('Update')</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
{{ $verifications->links() }}
@endsection
