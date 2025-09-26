@extends('layouts.guest')

@section('content')
<div class="bg-white shadow rounded p-6 space-y-6">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-semibold">@lang('Forgot your password?')</h1>
        <p class="text-sm text-slate-500">@lang('Enter your email address and we will send you a reset link.')</p>
    </div>

    @if (session('status'))
        <div class="p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div class="space-y-1">
            <label class="text-sm font-medium" for="email">@lang('Email')</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded border p-2">
        </div>
        <button type="submit" class="w-full rounded bg-blue-600 py-2 text-white font-semibold">@lang('Email Password Reset Link')</button>
    </form>

    <p class="text-sm text-center text-slate-500">
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">@lang('Back to login')</a>
    </p>
</div>
@endsection
