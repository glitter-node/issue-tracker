<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-12">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-950">Sign in</h1>
            <p class="mt-2 text-sm text-slate-600">Use your existing account to access the issue workspace.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('login.attempt') }}">
                @csrf
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

                <label class="flex items-center gap-3 text-sm text-slate-600" for="remember">
                    <input class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900" id="remember" name="remember" type="checkbox" value="1">
                    <span>Remember me</span>
                </label>

                <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
                    Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>
