<section class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-950">Create Account</h1>
    <p class="mt-2 text-sm text-slate-600">Complete registration using your verified email address.</p>

    <form class="mt-8 space-y-5" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="register-name">Name</label>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="register-name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            @error('name')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="register-verified-email">Verified Email</label>
            <input class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700 shadow-sm" id="register-verified-email" type="email" value="{{ $email }}" disabled readonly>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="register-password">Password</label>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="register-password" name="password" type="password" required>
            @error('password')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="register-password-confirmation">Confirm Password</label>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="register-password-confirmation" name="password_confirmation" type="password" required>
        </div>

        <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
            Create Account
        </button>
    </form>
</section>
