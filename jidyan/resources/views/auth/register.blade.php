@extends('layouts.guest')

@section('content')
<div class="bg-white shadow rounded p-6 space-y-6">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-semibold">@lang('Create player account')</h1>
        <p class="text-sm text-slate-500">@lang('Sign up to showcase your skills, manage media, and apply to opportunities.')</p>
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

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div class="space-y-1">
            <label class="text-sm font-medium" for="name">@lang('Full name')</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="w-full rounded border p-2">
        </div>
        <div class="space-y-1">
            <label class="text-sm font-medium" for="email">@lang('Email')</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded border p-2">
        </div>
        <div class="space-y-1">
            <label class="text-sm font-medium" for="password">@lang('Password')</label>
            <input id="password" name="password" type="password" required class="w-full rounded border p-2">
        </div>
        <div class="space-y-1">
            <label class="text-sm font-medium" for="password_confirmation">@lang('Confirm Password')</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded border p-2">
        </div>
        <button type="submit" class="w-full rounded bg-green-600 py-2 text-white font-semibold">@lang('Register')</button>
    </form>

    <p class="text-sm text-center text-slate-500">
        @lang('Already registered?')
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">@lang('Login')</a>
    </p>
</div>
@endsection
