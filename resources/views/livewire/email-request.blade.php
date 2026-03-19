<section class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-950">Verify Your Email</h1>
    <p class="mt-2 text-sm text-slate-600">Enter your email address to receive a verification link before registration.</p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form class="mt-8 space-y-5" method="POST" action="{{ route('register.email.send') }}">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="register-email">Email</label>
            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0" id="register-email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" type="submit">
                Send Verification Email
            </button>
            <a class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950" href="{{ $cancelUrl }}">
                Cancel
            </a>
        </div>
    </form>
</section>
