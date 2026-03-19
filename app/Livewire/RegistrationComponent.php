<?php

namespace App\Livewire;

use App\Services\EmailVerificationService;
use Livewire\Component;

class RegistrationComponent extends Component
{
    public string $email = '';

    public function mount(): void
    {
        $email = session(EmailVerificationService::SESSION_KEY);

        if (! is_string($email) || $email === '') {
            $this->redirectRoute('register.email', navigate: true);

            return;
        }

        $this->email = $email;
    }

    public function render()
    {
        return view('livewire.registration')
            ->layout('components.layouts.app')
            ->title('Register');
    }
}
