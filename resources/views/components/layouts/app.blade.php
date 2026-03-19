<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    @include('components.seo-head')
    <script src="{{ asset('theme.js') }}"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white/95 shadow-sm">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <div>
                <a class="text-2xl font-semibold tracking-tight text-slate-950" href="{{ route('issues.index') }}">
                    {{ config('app.name') }}
                </a>
                <p class="text-sm text-slate-500">Livewire issue management workspace</p>
            </div>
            <div class="flex items-center gap-3">
                @include('components.theme-switcher', ['id' => 'app-theme-switcher'])
                @auth
                    <div class="text-right text-sm">
                        <p class="font-medium text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                    <nav class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1.5 text-sm font-medium">
                        <a class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-white hover:text-slate-950" href="{{ route('dashboard') }}">Dashboard</a>
                        <a class="rounded-lg px-3 py-2 text-slate-600 transition hover:bg-white hover:text-slate-950" href="{{ route('issues.index') }}">Workspace</a>
                    </nav>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
                            Logout
                        </button>
                    </form>
                @else
                    <a class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700" href="{{ route('login') }}">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="space-y-8">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
