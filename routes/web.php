<?php

use App\Livewire\Dashboard;
use App\Livewire\EmailRequestComponent;
use App\Livewire\IssueWorkspace;
use App\Livewire\RegistrationComponent;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Support\SafeBackUrl;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', function (Request $request) {
        return view('auth.login', [
            'cancelUrl' => SafeBackUrl::for($request),
        ]);
    })->name('login');
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'auth' => 'Invalid email or password.',
            ])->onlyInput('email', 'remember');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    })->middleware('throttle:login')->name('login.attempt');
    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
    Route::post('/forgot-password', function (Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = PasswordBroker::sendResetLink(
            $request->only('email')
        );

        return $status === PasswordBroker::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    })->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', function (string $token, Request $request) {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    })->name('password.reset');
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    })->name('password.update');
    Route::get('/register/email', EmailRequestComponent::class)->name('register.email');
    Route::post('/register/email', function (Request $request, EmailVerificationService $verificationService) {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $verificationService->sendVerificationEmail(strtolower($validated['email']));

        return back()->with('status', 'A verification link has been sent to your email address.');
    })->middleware('throttle:6,1')->name('register.email.send');
    Route::get('/register/verify/{token}', function (string $token, Request $request, EmailVerificationService $verificationService) {
        $email = $verificationService->verifyToken($token);

        if ($email === null) {
            return redirect()->route('register.email')->withErrors([
                'email' => 'The verification link is invalid or has expired.',
            ]);
        }

        $request->session()->put(EmailVerificationService::SESSION_KEY, $email);

        return redirect()->route('register');
    })->name('register.verify');
    Route::get('/register', RegistrationComponent::class)->name('register');
    Route::post('/register', function (Request $request, EmailVerificationService $verificationService) {
        $verifiedEmail = $request->session()->get(EmailVerificationService::SESSION_KEY);

        if (! is_string($verifiedEmail) || $verifiedEmail === '' || ! $verificationService->isVerifiedEmail($verifiedEmail)) {
            return redirect()->route('register.email')->withErrors([
                'email' => 'Verify your email address before registering.',
            ]);
        }

        if (User::query()->where('email', $verifiedEmail)->exists()) {
            $verificationService->clearVerification($verifiedEmail);
            $request->session()->forget(EmailVerificationService::SESSION_KEY);

            return redirect()->route('register.email')->withErrors([
                'email' => 'That email address is already registered.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $verifiedEmail,
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        $verificationService->clearVerification($verifiedEmail);
        $request->session()->forget(EmailVerificationService::SESSION_KEY);

        return redirect()->route('login')->with('status', 'Your account has been created. You can now sign in.');
    })->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/issues', IssueWorkspace::class)->name('issues.index');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
