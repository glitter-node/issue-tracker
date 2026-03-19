@php($title = 'Home')
@section('meta_description', 'Simple issue tracking for small teams with real-time collaboration.')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        @include('components.seo-head')
        <script src="{{ asset('theme.js') }}"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="relative overflow-hidden">
            <div class="theme-backdrop absolute inset-0"></div>

            <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-8 lg:px-8">
                <header class="flex items-center justify-between border-b border-white/10 pb-6">
                    <a href="{{ url('/') }}" class="text-lg font-semibold tracking-[0.2em] text-cyan-300 uppercase">
                        {{ config('app.name') }}
                    </a>

                    <nav class="flex items-center gap-3 text-sm font-medium">
                        @include('components.theme-switcher', ['id' => 'landing-theme-switcher'])
                        <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-slate-200 transition hover:border-cyan-300 hover:text-cyan-200">
                            Login
                        </a>
                        <a href="{{ route('register.email') }}" class="rounded-full bg-cyan-400 px-4 py-2 text-slate-950 transition hover:bg-cyan-300">
                            Register
                        </a>
                    </nav>
                </header>

                <main class="flex flex-1 flex-col justify-center py-16 lg:py-24">
                    <section class="panel surface-2 rounded-3xl p-8 backdrop-blur sm:p-10">
                        <div class="space-y-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300/80">
                                Internal Delivery Workspace
                            </p>
                            <h1 class="max-w-3xl text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                                Simple issue tracking for small teams
                            </h1>
                            <p class="max-w-2xl text-lg leading-8 text-slate-300">
                                Track work, assign ownership, and keep discussions where they belong.
                            </p>
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="{{ route('register.email') }}" class="rounded-full bg-cyan-400 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                                    Get started
                                </a>
                            </div>
                        </div>
                    </section>

                    <section class="card surface-1 mt-10 rounded-2xl p-5 sm:p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Use case</h2>
                        <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-200 sm:text-base">
                            <li>For small teams without complex tools</li>
                            <li>For tracking issues without overhead</li>
                            <li>For keeping work and discussion in one place</li>
                        </ul>
                    </section>

                    <section class="section surface-3 mt-10 rounded-2xl p-6 sm:p-8">
                        <p class="text-lg font-medium text-white">Start tracking your work in minutes.</p>
                        <div class="mt-5 flex flex-wrap items-center gap-4">
                            <a href="{{ route('register.email') }}" class="rounded-full bg-cyan-400 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                                Register
                            </a>
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:border-cyan-300 hover:text-cyan-200">
                                Login
                            </a>
                        </div>
                    </section>
                </main>

                <footer class="flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <p>{{ config('app.name') }} is an authenticated issue-tracking workspace for internal teams.</p>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="transition hover:text-cyan-200">Login</a>
                        <a href="{{ route('register.email') }}" class="transition hover:text-cyan-200">Register</a>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
