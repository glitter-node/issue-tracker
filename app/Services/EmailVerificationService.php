<?php

namespace App\Services;

use App\Mail\RegistrationVerificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public const SESSION_KEY = 'verified_registration_email';

    public function sendVerificationEmail(string $email): void
    {
        DB::table('email_verifications')
            ->where('email', $email)
            ->delete();

        $token = Str::random(64);

        DB::table('email_verifications')->insert([
            'email' => $email,
            'token' => $token,
            'verified_at' => null,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new RegistrationVerificationMail(
            URL::route('register.verify', ['token' => $token]),
        ));
    }

    public function verifyToken(string $token): ?string
    {
        $record = DB::table('email_verifications')
            ->where('token', $token)
            ->whereNull('verified_at')
            ->where('created_at', '>=', now()->subHour())
            ->first();

        if ($record === null) {
            return null;
        }

        DB::table('email_verifications')
            ->where('id', $record->id)
            ->update([
                'verified_at' => now(),
            ]);

        return $record->email;
    }

    public function isVerifiedEmail(string $email): bool
    {
        return DB::table('email_verifications')
            ->where('email', $email)
            ->whereNotNull('verified_at')
            ->exists();
    }

    public function clearVerification(string $email): void
    {
        DB::table('email_verifications')
            ->where('email', $email)
            ->delete();
    }
}
