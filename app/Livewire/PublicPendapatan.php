<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicPendapatan extends Component
{
    #[Url(as: 'tahun')]
    public $tahun;

    public function mount()
    {
        if (!$this->tahun) {
            $this->tahun = (int) request()->query('tahun', date('Y'));
        }
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        // Retrieve the structured and filtered data from repository
        $pendapatanData = $repository->getPendapatanTree($this->tahun);
        $lastUpdate = $repository->getLastUpdatePendapatan($this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        return view('livewire.public-pendapatan', [
            'pendapatanData' => $pendapatanData,
            'tahun' => $this->tahun,
            'lastUpdate' => $lastUpdate,
            'availableYears' => $availableYears,
        ]);
    }
}
