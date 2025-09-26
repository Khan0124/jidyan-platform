@extends('layouts.guest')

@section('content')
<div class="bg-white shadow rounded p-6 space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold">@lang('Verify your email')</h1>
        <p class="text-sm text-slate-500">@lang('Thanks for signing up! Before getting started, please confirm your email address by clicking on the link we just emailed you.')</p>
    </div>

    @if (session('status'))
        <div class="p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
        @csrf
        <button type="submit" class="w-full rounded bg-blue-600 py-2 text-white font-semibold">@lang('Resend verification email')</button>
    </form>

    <div class="text-sm text-center text-slate-500 space-y-2">
        <p>@lang('Entered the wrong email?')</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-600 hover:underline">@lang('Logout')</button>
        </form>
    </div>
</div>
@endsection
