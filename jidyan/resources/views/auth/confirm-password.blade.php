@extends('layouts.guest')

@section('content')
<div class="bg-white shadow rounded p-6 space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold">@lang('Confirm password')</h1>
        <p class="text-sm text-slate-500">@lang('For your security, please confirm your password before continuing.')</p>
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

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div class="space-y-1">
            <label class="text-sm font-medium" for="password">@lang('Password')</label>
            <input id="password" name="password" type="password" required class="w-full rounded border p-2">
        </div>
        <button type="submit" class="w-full rounded bg-blue-600 py-2 text-white font-semibold">@lang('Confirm')</button>
    </form>
</div>
@endsection
