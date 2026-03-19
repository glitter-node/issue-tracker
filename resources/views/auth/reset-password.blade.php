@php($title = 'Reset Password')
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
                @include('components.theme-switcher', ['id' => 'reset-password-theme-switcher'])
            </div>
            <h1 class="text-2xl font-semibold text-slate-950">Reset password</h1>
            <p class="mt-2 text-sm text-slate-600">Choose a new password for your account.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus>
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="password">New Password</label>
                    <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="password" name="password" type="password" required>
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="password_confirmation">Confirm Password</label>
                    <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
                    Reset Password
                </button>
            </form>
        </section>
    </main>
</body>
</html>
