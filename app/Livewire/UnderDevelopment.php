<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.frontend')]
class UnderDevelopment extends Component
{
    #[Url(as: 'feature')]
    public string $feature = '';

    public function mount(): void
    {
        $this->feature = (string) request()->query('feature', 'Fitur Ini');
    }

    public function render()
    {
        return view('livewire.under-development', [
            'title' => 'Dalam Pengembangan - ' . \App\Models\AppSetting::getAppName(),
        ]);
    }
}

