<?php

namespace App\Livewire;

use App\Support\SafeBackUrl;
use Livewire\Component;

class EmailRequestComponent extends Component
{
    public function render()
    {
        return view('livewire.email-request')
            ->with('cancelUrl', SafeBackUrl::for(request()))
            ->layout('components.layouts.app')
            ->title('Verify Email');
    }
}
