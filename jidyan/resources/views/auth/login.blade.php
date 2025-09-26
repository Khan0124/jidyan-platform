@extends('layouts.guest')

@section('content')
<div class="bg-white shadow rounded p-6 space-y-6">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-semibold">@lang('Login')</h1>
        <p class="text-sm text-slate-500">@lang('Access your dashboard to manage your football journey.')</p>
    </div>

    @if ($errors->any())
        <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div class="space-y-1">
            <label class="text-sm font-medium" for="email">@lang('Email')</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded border p-2">
        </div>
        <div class="space-y-1">
            <label class="text-sm font-medium" for="password">@lang('Password')</label>
            <input id="password" name="password" type="password" required class="w-full rounded border p-2">
        </div>
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" value="1" class="rounded border">
                <span>@lang('Remember me')</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">@lang('Forgot your password?')</a>
        </div>
        <button type="submit" class="w-full rounded bg-blue-600 py-2 text-white font-semibold">@lang('Login')</button>
    </form>

    <p class="text-sm text-center text-slate-500">
        @lang('No account yet?')
        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">@lang('Register')</a>
    </p>
</div>
@endsection
