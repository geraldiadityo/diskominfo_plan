<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicPendapatan extends Component
{
    public function render(DashboardRepositoryInterface $repository)
    {
        $tahun = date('Y');
        // Retrieve the structured and filtered data from repository
        $pendapatanData = $repository->getPendapatanTree($tahun);

        return view('livewire.public-pendapatan', [
            'pendapatanData' => $pendapatanData,
            'tahun' => $tahun,
            'lastUpdate' => date('Y-m-d H:i:s')
        ]);
    }
}
