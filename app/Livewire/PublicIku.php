<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicIku extends Component
{
    public $years = [2021, 2022, 2023, 2024, 2025, 2026];

    public function render(DashboardRepositoryInterface $repository)
    {
        $ikuData = $repository->getIkuData($this->years);

        return view('livewire.public-iku', [
            'ikuData' => $ikuData,
        ]);
    }
}
