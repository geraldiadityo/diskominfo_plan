<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class IndexPendapatan extends Component
{
    public $tahun;
    
    public function mount()
    {
        $this->tahun = date('Y');
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpds = $repository->getAllSkpdPendapatanSummary($this->tahun);

        // Sort descending by percentage
        $skpds = $skpds->sortByDesc('persentase')->values();

        $summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun);

        return view('livewire.index-pendapatan', [
            'skpds' => $skpds,
            'summaryByJenis' => $summaryByJenis,
        ]);
    }
}
