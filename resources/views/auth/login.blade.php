@php($title = 'Login')
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    @include('components.seo-head')
    <script src="{{ asset('theme.js') }}"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-12">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-6 flex justify-end">
                @include('components.theme-switcher', ['id' => 'login-theme-switcher'])
            </div>
            <h1 class="text-2xl font-semibold text-slate-950">Sign in</h1>
            <p class="mt-2 text-sm text-slate-600">Use your existing account to access the issue workspace.</p>

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('session_expired'))
                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    Your session expired. Please continue.
                </div>
            @endif

            <form class="mt-8 space-y-5" method="POST" action="{{ route('login.attempt') }}">
                @csrf
                @error('auth')
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="password">Password</label>
                    <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="password" name="password" type="password" required>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                    <label class="flex items-center gap-3" for="remember">
                        <input class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                        <span>Remember me</span>
                    </label>
                    <a class="text-sm text-slate-600 underline underline-offset-4 transition hover:text-slate-900" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
                        Login
                    </button>
                    <a class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950" href="{{ $cancelUrl }}">
                        Cancel
                    </a>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Need an account?
                <a class="font-medium text-slate-900 underline underline-offset-4" href="{{ route('register.email') }}">Verify your email to register</a>
            </p>
        </section>
    </main>
</body>
</html>
