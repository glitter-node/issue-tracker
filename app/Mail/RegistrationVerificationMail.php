<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class RegistrationVerificationMail extends Mailable
{
    use Queueable;

    public function __construct(public string $verificationUrl) {}

    public function build(): self
    {
        return $this->subject('Verify your email to continue registration')
            ->text('emails.registration-verification');
    }
}
