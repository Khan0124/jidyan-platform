<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Jidyan') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white shadow">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="{{ route('home') }}" class="text-xl font-semibold">{{ config('app.name') }}</a>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-slate-500">@lang('Language')</span>
                        <form method="POST" action="{{ route('locale.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="en">
                            <button type="submit"
                                class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-slate-200 font-semibold text-slate-900' : 'text-slate-500 hover:text-slate-700' }}">
                                @lang('English')
                            </button>
                        </form>
                        <form method="POST" action="{{ route('locale.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="ar">
                            <button type="submit"
                                class="px-2 py-1 rounded {{ app()->getLocale() === 'ar' ? 'bg-slate-200 font-semibold text-slate-900' : 'text-slate-500 hover:text-slate-700' }}">
                                @lang('Arabic')
                            </button>
                        </form>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        @auth
                            <span>{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-700">@lang('Logout')</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700">@lang('Login')</a>
                            <a href="{{ route('register') }}" class="text-green-600 hover:text-green-700">@lang('Register')</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-1 container mx-auto px-4 py-6">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <footer class="bg-white border-t py-4 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </footer>
    </div>
    @stack('scripts')
    @livewireScripts
</body>
</html>
